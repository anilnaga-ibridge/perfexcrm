<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleMenu;
use App\Models\ModulePermission;
use App\Models\Permission;
use App\Models\Role;
use App\Events\ModuleActivated;
use App\Events\ModuleDeactivated;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Exception;
use ZipArchive;

class ModuleManager
{
    /**
     * Resolve the module's filesystem path.
     * Uses alias-based path with backward-compatible fallback to name-based path.
     */
    private static function modulePath(string $alias, ?string $name = null): string
    {
        $path = base_path("Modules/{$alias}");
        if (is_dir($path)) {
            return $path;
        }
        // Legacy fallback: modules installed with display-name-based directories
        if ($name !== null) {
            $legacyPath = base_path("Modules/{$name}");
            if (is_dir($legacyPath)) {
                return $legacyPath;
            }
        }
        return $path;
    }

    /**
     * Install or prepare a module from an uploaded ZIP file.
     *
     * @throws Exception
     */
    public static function install(string $zipPath): Module
    {
        // 1. Fast pre-check: Verify ZIP contains a valid manifest, PHP module file, or nested archive before extracting
        $zipCheck = new ZipArchive();
        if ($zipCheck->open($zipPath) !== true) {
            throw new Exception("Failed to open module ZIP file.");
        }

        $hasCandidate = false;
        for ($i = 0; $i < $zipCheck->numFiles; $i++) {
            $entryName = $zipCheck->getNameIndex($i);
            $baseName = strtolower(basename($entryName));
            if ($baseName === 'module.json' || $baseName === 'manifest.json' || str_ends_with($baseName, '.php') || str_ends_with($baseName, '.zip')) {
                $hasCandidate = true;
                break;
            }
        }
        $zipCheck->close();

        if (!$hasCandidate) {
            throw new Exception("Invalid module ZIP: No 'module.json' manifest, legacy PHP module header, or nested package found in archive.");
        }

        // 2. Verify digital signature first
        try {
            resolve(\App\Plugin\Kernel\PackageManager::class)->verifySignature($zipPath);
        } catch (\Throwable $e) {
            throw new Exception("Package signature verification failed: " . $e->getMessage());
        }

        $tempDir = storage_path('app/temp_module_extract_' . microtime(true) . '_' . uniqid());
        File::ensureDirectoryExists($tempDir);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            File::deleteDirectory($tempDir);
            throw new Exception("Failed to open module ZIP file.");
        }

        $zip->extractTo($tempDir);
        $zip->close();

        // Security: validate no files were extracted outside the temp directory (Zip Slip protection)
        $realTempDir = realpath($tempDir);
        if ($realTempDir !== false) {
            $normalizedTempDir = rtrim(str_replace('\\', '/', $realTempDir), '/') . '/';
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }
                $realPath = realpath($file->getPathname());
                $normalizedRealPath = $realPath !== false ? str_replace('\\', '/', $realPath) : false;

                if ($normalizedRealPath === false || !str_starts_with($normalizedRealPath, $normalizedTempDir)) {
                    @unlink($file->getPathname());
                    Log::warning("Zip Slip detected: file {$file->getPathname()} was outside extraction directory");
                }
            }
        }

        // Find module.json inside temp dir or parse PHP comment headers
        $manifestPath = self::findFileInDir($tempDir, 'module.json');
        if ($manifestPath) {
            $manifestContent = file_get_contents($manifestPath);
            $info = json_decode($manifestContent, true);
            if (!$info || !is_array($info)) {
                $jsonErr = json_last_error_msg();
                File::deleteDirectory($tempDir);
                throw new Exception("Invalid module manifest: 'module.json' could not be decoded. Error: {$jsonErr}. Content: '{$manifestContent}'");
            }
            $moduleDir = dirname($manifestPath);
        } else {
            // Fallback: search for any PHP file containing WordPress/iBridge style comment headers
            $phpFiles = self::findPhpManifestFiles($tempDir);

            // If no PHP files found, look for nested ZIP files (CodeCanyon distribution pattern)
            if (empty($phpFiles)) {
                $nestedZips = self::findNestedZipFiles($tempDir);
                foreach ($nestedZips as $nestedZipPath) {
                    $nestedZip = new ZipArchive();
                    if ($nestedZip->open($nestedZipPath) === true) {
                        $nestedZip->extractTo($tempDir);
                        $nestedZip->close();
                        $manifestPath = self::findFileInDir($tempDir, 'module.json');
                        if ($manifestPath) {
                            $manifestContent = file_get_contents($manifestPath);
                            $info = json_decode($manifestContent, true);
                            if (!$info || !is_array($info)) {
                                File::deleteDirectory($tempDir);
                                throw new Exception("Invalid module manifest: 'module.json' could not be decoded.");
                            }
                            $moduleDir = dirname($manifestPath);
                            break;
                        } else {
                            $phpFiles = self::findPhpManifestFiles($tempDir);
                            if (!empty($phpFiles)) {
                                break;
                            }
                        }
                    }
                }
            }

            if (empty($phpFiles) && !isset($info)) {
                File::deleteDirectory($tempDir);
                throw new Exception("Invalid module ZIP: Missing 'module.json' manifest or main PHP module file.");
            }

            if (!isset($info)) {
                $info = null;
                foreach ($phpFiles as $phpFile) {
                    $parsed = self::parsePhpHeaders($phpFile);
                    if ($parsed) {
                        $info = $parsed;
                        $manifestPath = $phpFile;
                        break;
                    }
                }
                if (!$info) {
                    File::deleteDirectory($tempDir);
                    throw new Exception("Invalid module ZIP: Missing 'module.json' and no valid PHP module headers found.");
                }
                $moduleDir = dirname($manifestPath);
            }
        }

        // Normalize alias early (before duplicate check and DB write)
        $info['alias'] = ModuleValidator::normalizeAlias($info['alias']);
        $alias = $info['alias'];

        // Check if module with same alias already exists
        $existing = Module::where('alias', $alias)->first();
        if ($existing) {
            File::deleteDirectory($tempDir);
            return self::upgrade($existing, $zipPath);
        }

        // Run validation for new modules (upgrade uses validateManifest with ID exclusion internally)
        ModuleValidator::validateManifest($info);

        // Re-read alias (validateManifest normalizes it idempotently)
        $alias = $info['alias'];
        $name = $info['name'];
        $version = $info['version'];
        $minCoreVersion = $info['minimum_core_version'] ?? '1.0.0';
        $depends = $info['depends'] ?? [];

        // Check minimum core version (assume core version is 3.0.0 for this environment)
        $coreVersion = '3.0.0';
        if (version_compare($coreVersion, $minCoreVersion, '<')) {
            File::deleteDirectory($tempDir);
            throw new Exception("Module requires core system version {$minCoreVersion} or higher. Current core version is {$coreVersion}.");
        }

        // Copy files to target Modules/{ModuleAlias} path
        $targetPath = base_path("Modules/{$alias}");
        if (File::exists($targetPath)) {
            File::deleteDirectory($targetPath);
        }
        File::ensureDirectoryExists(base_path('Modules'));
        File::copyDirectory($moduleDir, $targetPath);
        File::deleteDirectory($tempDir);

        // Auto-migrate legacy Vue files to SDK format
        self::migrateLegacyVueFiles($alias);

        // Save module record in database
        $module = Module::create([
            'name' => $name,
            'alias' => $alias,
            'version' => $version,
            'minimum_core_version' => $minCoreVersion,
            'depends' => $depends,
            'description' => $info['description'] ?? '',
            'status' => 'installed',
            'author' => $info['author'] ?? 'Admin',
        ]);

        // Run legacy CodeIgniter installer script and migrations under the Laravel compatibility layer
        try {
            self::runLegacyInstallerAndMigrations($alias);
        } catch (\Exception $legacyEx) {
            \Illuminate\Support\Facades\Log::warning("Legacy compatibility installer failed for [{$alias}]: " . $legacyEx->getMessage());
        }

        // Run migrations ONCE during installation
        $migrationsPath = "Modules/{$alias}/Database/Migrations";
        if (File::exists(base_path($migrationsPath))) {
            Artisan::call('migrate', [
                '--path' => $migrationsPath,
                '--realpath' => false,
            ]);
        }
        try {
            resolve(\App\Services\PluginBridgeService::class)->clearCache($alias);
        } catch (\Throwable $e) {}
        ModuleSettingsService::flushSchemaCache($module);
        try {
            event(new \App\Events\ModuleInstalled($module));
        } catch (\Exception $e) {
            // Ignore listener crashes
        }

        return $module;
    }

    /**
     * Activate a module.
     *
     * @throws Exception
     */
    public static function activate(string $id): Module
    {
        $module = Module::findOrFail($id);

        if ($module->status === 'active') {
            // Only skip if menus are already registered.
            // If active but has 0 menus (e.g. legacy plugin uploaded without menu.json),
            // fall through and register menus now so the sidebar is populated.
            $hasMenus = ModuleMenu::where('module_id', $module->id)->exists();
            if ($hasMenus) {
                return $module;
            }
        }


        // Dependency check (reads only — outside transaction)
        if (!empty($module->depends)) {
            foreach ($module->depends as $depAlias) {
                $dependency = Module::where('alias', $depAlias)->first();
                if (!$dependency || $dependency->status !== 'active') {
                    throw new Exception("Cannot activate module: Dependency module '{$depAlias}' must be installed and active.");
                }
            }
        }

        // Filesystem checks and reads (outside transaction)
        ModuleValidator::validateHealth($module->alias, $module->name);
        $modulePath = self::modulePath($module->alias, $module->name);

        $permissions = [];
        $permissionsPath = "{$modulePath}/permissions.json";
        if (File::exists($permissionsPath)) {
            $permissionsContent = File::get($permissionsPath);
            $permissions = json_decode($permissionsContent, true);
            $permissions = is_array($permissions) ? $permissions : [];
        }

        $menu = null;
        $menuPath = "{$modulePath}/menu.json";
        if (File::exists($menuPath)) {
            $menuContent = File::get($menuPath);
            $menu = json_decode($menuContent, true);
            $menu = is_array($menu) ? $menu : null;
        }

        // Legacy compatibility layer: Auto-generate menu structure if missing
        if (!$menu) {
            $children = [
                [
                    'title' => 'Dashboard',
                    'route' => '/dashboard'
                ]
            ];

            // Scan views folder for potential page items
            $viewsDir = "{$modulePath}/views";
            if (!is_dir($viewsDir)) {
                $viewsDir = "{$modulePath}/Views";
            }

            if (is_dir($viewsDir)) {
                $subdirs = array_filter(glob($viewsDir . '/*'), 'is_dir');
                foreach ($subdirs as $subdir) {
                    $folderName = basename($subdir);
                    
                    // Ignore common helper/partial view directories
                    if (in_array(strtolower($folderName), ['includes', 'partials', 'layouts', 'admin', 'client', 'layout'])) {
                        continue;
                    }

                    // Format title: e.g. "timesheet_leaves" -> "Timesheet Leaves"
                    $title = ucwords(str_replace(['-', '_'], ' ', $folderName));
                    
                    $children[] = [
                        'title' => $title,
                        'route' => '/' . $folderName
                    ];
                }
            }

            $menu = [
                'title' => $module->name,
                'icon' => 'appstore',
                'children' => $children
            ];
        }

        // DB mutations in a transaction
        DB::transaction(function () use ($module, $permissions, $menu) {
            $adminRole = Role::where('slug', 'admin')->first();

            // Clean up any existing permission/menu records for this module to prevent duplicates
            ModuleMenu::where('module_id', $module->id)->delete();
            $oldPermNames = ModulePermission::where('module_id', $module->id)->pluck('permission_name')->toArray();
            if (!empty($oldPermNames)) {
                $oldPermIds = Permission::whereIn('name', $oldPermNames)->pluck('id')->toArray();
                if (!empty($oldPermIds)) {
                    DB::table('role_permissions')->whereIn('permission_id', $oldPermIds)->delete();
                    Permission::whereIn('id', $oldPermIds)->delete();
                }
                ModulePermission::where('module_id', $module->id)->delete();
            }

            foreach ($permissions as $perm) {
                $pName = $perm['key'] ?? $perm['name'] ?? null;
                if ($pName) {
                    $pDesc = $perm['description'] ?? '';
                    $permission = Permission::firstOrCreate(
                        ['name' => $pName],
                        ['description' => $pDesc]
                    );
                    ModulePermission::firstOrCreate([
                        'module_id' => $module->id,
                        'permission_name' => $pName,
                    ]);
                    if ($adminRole) {
                        DB::table('role_permissions')->insertOrIgnore([
                            'role_id' => $adminRole->id,
                            'permission_id' => $permission->id,
                        ]);
                    }
                }
            }

            if ($menu) {
                self::registerMenuNode($module->id, $menu);
            }

            $module->update(['status' => 'active']);
        });

        // Run migrations and install logic
        try {
            self::runLegacyInstallerAndMigrations($module->alias);
            self::publishAssets($module->alias);
        } catch (\Throwable $e) {
            $module->update(['status' => 'error']);
            \Illuminate\Support\Facades\Log::error("Module activation error for [{$module->alias}]: " . $e->getMessage());
            throw $e;
        }

        // Fire activation event (best-effort, outside transaction)
        try {
            event(new ModuleActivated($module));
        } catch (Exception $e) {
            // Ignore event listener crashes
        }

        return $module;
    }

    /**
     * Automatically publish static assets (CSS, JS, images, fonts, vendor, maps)
     * from Modules/{alias}/assets to public/modules/{alias}/assets
     */
    public static function publishAssets(string $alias): void
    {
        $modulePath = self::modulePath($alias);
        $sourceAssets = "{$modulePath}/assets";
        if (!is_dir($sourceAssets)) {
            return;
        }

        $targetAssets = public_path("modules/{$alias}/assets");
        File::ensureDirectoryExists(dirname($targetAssets));

        try {
            File::copyDirectory($sourceAssets, $targetAssets);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not publish assets for [{$alias}]: " . $e->getMessage());
        }
    }

    /**
     * Deactivate a module.
     */
    public static function deactivate(string $id): Module
    {
        $module = Module::findOrFail($id);

        if ($module->status !== 'active') {
            return $module;
        }

        // Deactivate dependents first (each recursive call has its own transaction)
        $dependents = Module::where('status', 'active')->get();
        foreach ($dependents as $dep) {
            if (is_array($dep->depends) && in_array($module->alias, $dep->depends)) {
                self::deactivate($dep->id);
            }
        }

        // DB mutations in a transaction
        DB::transaction(function () use ($module) {
            ModuleMenu::where('module_id', $module->id)->delete();

            $permNames = ModulePermission::where('module_id', $module->id)->pluck('permission_name')->toArray();
            if (!empty($permNames)) {
                $permissionIds = Permission::whereIn('name', $permNames)->pluck('id')->toArray();
                if (!empty($permissionIds)) {
                    DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
                    Permission::whereIn('id', $permissionIds)->delete();
                }
                ModulePermission::where('module_id', $module->id)->delete();
            }

            $module->update(['status' => 'inactive']);
        });

        // Fire deactivation event (best-effort, outside transaction)
        try {
            event(new ModuleDeactivated($module));
        } catch (Exception $e) {
            // Ignore event listener crashes
        }

        return $module;
    }

    /**
     * Upgrade an existing module using an uploaded ZIP.
     *
     * @throws Exception
     */
    public static function upgrade(Module $existing, string $zipPath): Module
    {
        $tempDir = storage_path('app/temp_module_extract_' . time());
        File::ensureDirectoryExists($tempDir);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            File::deleteDirectory($tempDir);
            throw new Exception("Failed to open upgrade ZIP.");
        }
        $zip->extractTo($tempDir);
        $zip->close();

        $manifestPath = self::findFileInDir($tempDir, 'module.json');
        if ($manifestPath) {
            $info = json_decode(file_get_contents($manifestPath), true);
            if (!$info) {
                File::deleteDirectory($tempDir);
                throw new Exception("Invalid module manifest in upgrade ZIP.");
            }
            $moduleDir = dirname($manifestPath);
        } else {
            $phpFiles = self::findPhpManifestFiles($tempDir);
            $info = null;
            foreach ($phpFiles as $phpFile) {
                $parsed = self::parsePhpHeaders($phpFile);
                if ($parsed) {
                    $info = $parsed;
                    $manifestPath = $phpFile;
                    $moduleDir = dirname($manifestPath);
                    break;
                }
            }
            if (!$info) {
                File::deleteDirectory($tempDir);
                throw new Exception("Invalid upgrade ZIP: Missing 'module.json' and no valid PHP module headers found.");
            }
        }

        // Validate manifest (excluding uniqueness check against self)
        ModuleValidator::validateManifest($info, $existing->id);

        $newVersion = $info['version'];
        if (version_compare($newVersion, $existing->version, '<=')) {
            File::deleteDirectory($tempDir);
            throw new Exception("Uploaded module version ({$newVersion}) must be higher than current installed version ({$existing->version}).");
        }

        $moduleDir = dirname($manifestPath);
        // Always deploy to alias-based path
        $targetPath = base_path("Modules/{$existing->alias}");
        // Remove legacy name-based directory if it exists and differs
        $legacyPath = base_path("Modules/{$existing->name}");
        if ($legacyPath !== $targetPath && File::exists($legacyPath)) {
            File::deleteDirectory($legacyPath);
        }

        // Snapshot transitive dependents before deactivation cascade
        $wasActive = ($existing->status === 'active');
        $dependentSnapshot = [];

        if ($wasActive) {
            $dependentSnapshot = self::collectTransitiveDependents($existing->alias);
            $dependentSnapshot = self::topologicallySortDependents($dependentSnapshot, $existing->alias);
            self::deactivate($existing->id);
        }

        // Create rollback snapshot
        $pm = resolve(\App\Plugin\Kernel\PackageManager::class);
        $snapshotPath = '';
        try {
            $snapshotPath = $pm->createSnapshot($existing->alias, $existing->version);
        } catch (\Throwable $e) {
            Log::warning("PackageManager: Failed to create snapshot for rollback: " . $e->getMessage());
        }

        try {
            // Replace codebase directory
            File::deleteDirectory($targetPath);
            File::copyDirectory($moduleDir, $targetPath);
            File::deleteDirectory($tempDir);
        } catch (\Throwable $upgradeEx) {
            if (!empty($snapshotPath) && File::exists($snapshotPath)) {
                $pm->rollback($existing->alias, $snapshotPath);
            }
            File::deleteDirectory($tempDir);
            throw new Exception("Upgrade directory extraction failed. Rolled back successfully. Error: " . $upgradeEx->getMessage());
        }

        // Run upgrade.php or version upgrade files if available
        $upgradeScript = "{$targetPath}/upgrade.php";
        if (File::exists($upgradeScript)) {
            try {
                include $upgradeScript;
            } catch (Exception $e) {
                // Log upgrade script error
            }
        }

        // Check for specific version upgrade scripts (e.g. 1.0_to_1.1.php)
        $upgradesDir = "{$targetPath}/Upgrades";
        if (is_dir($upgradesDir)) {
            $files = glob("{$upgradesDir}/*.php");
            sort($files);
            foreach ($files as $file) {
                // Check filename matches schema like 1.0_to_1.1.php or version triggers
                // For simplicity, we execute all PHP upgrade files sequentially
                try {
                    include $file;
                } catch (Exception $e) {
                    // Log specific upgrade script error
                }
            }
        }

        // Run migrations
        $migrationsPath = "Modules/{$existing->alias}/Database/Migrations";
        if (File::exists(base_path($migrationsPath))) {
            Artisan::call('migrate', [
                '--path' => $migrationsPath,
                '--realpath' => false,
            ]);
        }

        // Update database info
        $existing->update([
            'version' => $newVersion,
            'description' => $info['description'] ?? $existing->description,
            'depends' => $info['depends'] ?? $existing->depends,
            'minimum_core_version' => $info['minimum_core_version'] ?? $existing->minimum_core_version,
            'status' => 'installed',
        ]);

        // Re-activate parent, then restore dependents in dependency order
        if ($wasActive) {
            self::activate($existing->id);
            $existing->refresh();

            $depIds = array_column($dependentSnapshot, 'id');
            $depModules = Module::whereIn('id', $depIds)->get()->keyBy('id');

            foreach ($dependentSnapshot as $entry) {
                $dep = $depModules[$entry['id']] ?? null;
                if (!$dep) {
                    \Illuminate\Support\Facades\Log::warning(
                        "Dependent module '{$entry['alias']}' (ID: {$entry['id']}) not found after upgrade. Skipping reactivation."
                    );
                    continue;
                }
                if ($dep->status === 'inactive') {
                    self::activate($dep->id);
                }
            }
        }

        ModuleSettingsService::flushSchemaCache($existing);

        try {
            event(new \App\Events\ModuleUpgraded($existing));
        } catch (\Exception $e) {
            // Ignore listener crashes
        }

        return $existing;
    }

    /**
     * Collect all modules that transitively depend on the given alias and are currently active.
     * Returns a structured snapshot to enable deterministic restoration after upgrade.
     */
    private static function collectTransitiveDependents(string $alias): array
    {
        $snapshot = [];
        $queue = [$alias];
        $seen = [$alias => true];

        $allActive = Module::where('status', 'active')->get();

        while (!empty($queue)) {
            $current = array_shift($queue);
            foreach ($allActive as $m) {
                if (is_array($m->depends) && in_array($current, $m->depends)) {
                    if (!isset($seen[$m->alias])) {
                        $seen[$m->alias] = true;
                        $snapshot[] = [
                            'id' => $m->id,
                            'alias' => $m->alias,
                            'was_active' => true,
                        ];
                        $queue[] = $m->alias;
                    }
                }
            }
        }

        return $snapshot;
    }

    /**
     * Sort dependent modules so that activation respects inter-dependency order.
     * Uses Kahn's algorithm for topological sort on the dependency graph.
     *
     * @throws Exception if a circular dependency is detected.
     */
    private static function topologicallySortDependents(array $snapshot, string $parentAlias): array
    {
        if (empty($snapshot)) {
            return [];
        }

        $ids = array_column($snapshot, 'id');
        $modules = Module::whereIn('id', $ids)->get()->keyBy('id');

        // Build alias → id lookup (includes parent for dependency edge resolution)
        $aliasToId = [];
        foreach ($modules as $m) {
            $aliasToId[$m->alias] = $m->id;
        }
        $parent = Module::where('alias', $parentAlias)->first();
        if ($parent) {
            $aliasToId[$parentAlias] = $parent->id;
        }

        // Build adjacency and in-degree: edge $depId → $m->id
        // means $depId must be activated before $m
        $inDegree = array_fill_keys($ids, 0);
        $graph = [];

        foreach ($modules as $m) {
            if (!is_array($m->depends)) {
                continue;
            }
            foreach ($m->depends as $depAlias) {
                $depId = $aliasToId[$depAlias] ?? null;
                if ($depId !== null && in_array($depId, $ids)) {
                    $graph[$depId][] = $m->id;
                    $inDegree[$m->id]++;
                }
            }
        }

        // Kahn's algorithm
        $queue = [];
        foreach ($ids as $id) {
            if (($inDegree[$id] ?? 0) === 0) {
                $queue[] = $id;
            }
        }

        $sorted = [];
        while (!empty($queue)) {
            $current = array_shift($queue);
            $sorted[] = $current;
            if (isset($graph[$current])) {
                foreach ($graph[$current] as $neighbor) {
                    $inDegree[$neighbor]--;
                    if ($inDegree[$neighbor] === 0) {
                        $queue[] = $neighbor;
                    }
                }
            }
        }

        if (count($sorted) !== count($ids)) {
            throw new Exception('Circular dependency detected among dependent modules during upgrade.');
        }

        // Reorder snapshot to match the computed topological order
        $rank = array_flip($sorted);
        usort($snapshot, function (array $a, array $b) use ($rank): int {
            return ($rank[$a['id']] ?? PHP_INT_MAX) <=> ($rank[$b['id']] ?? PHP_INT_MAX);
        });

        return $snapshot;
    }

    /**
     * Uninstall a module.
     */
    public static function uninstall(string $id, bool $deleteData): void
    {
        $module = Module::findOrFail($id);

        // Ensure it is deactivated first
        self::deactivate($module->id);

        $modulePath = self::modulePath($module->alias, $module->name);

        // Trigger uninstall.php script if it exists
        $uninstallScript = "{$modulePath}/uninstall.php";
        if (File::exists($uninstallScript)) {
            try {
                include $uninstallScript;
            } catch (Exception $e) {
                // Log script error
            }
        }

        // Handle database tables backup and rollback
        if ($deleteData) {
            self::backupAndDropModuleTables($module->alias, $module->name);
        }

        // Remove legacy CI symlink
        self::removeLegacySymlink($module->alias);

        // Delete codebase files
        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }
        try {
            resolve(\App\Services\PluginBridgeService::class)->clearCache($module->alias);
        } catch (\Throwable $e) {}
        ModuleSettingsService::flushSchemaCache($module);
        try {
            event(new \App\Events\ModuleUninstalled($module));
        } catch (\Exception $e) {
            // Ignore listener crashes
        }

        // Delete DB record (cascades to module_settings, module_permissions, module_menus)
        $module->delete();
    }

    /**
     * Back up data from the module's custom tables and then roll back/drop the tables.
     */
    private static function backupAndDropModuleTables(string $moduleAlias, ?string $moduleName = null): void
    {
        $modulePath = base_path("Modules/{$moduleAlias}");
        if (!is_dir($modulePath) && $moduleName !== null) {
            $modulePath = base_path("Modules/{$moduleName}");
        }
        if (!is_dir($modulePath)) {
            return;
        }

        // Collect table names from multiple sources
        $tables = [];

        // 1. Scan Laravel migration files (Database/Migrations/*.php)
        $laravelMigrationsDir = "{$modulePath}/Database/Migrations";
        if (is_dir($laravelMigrationsDir)) {
            $files = glob("{$laravelMigrationsDir}/*.php");
            foreach ($files as $file) {
                $content = File::get($file);
                if (preg_match_all("/Schema::create\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                    foreach ($matches[1] as $table) {
                        if (!in_array($table, $tables)) {
                            $tables[] = $table;
                        }
                    }
                }
            }
        }

        // 2. Scan CI-style migration files (migrations/*.php)
        $ciMigrationsDir = "{$modulePath}/migrations";
        if (is_dir($ciMigrationsDir)) {
            $files = glob("{$ciMigrationsDir}/*.php");
            foreach ($files as $file) {
                $content = File::get($file);
                // CI uses: $this->dbforge->create_table('table_name')
                if (preg_match_all("/create_table\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                    foreach ($matches[1] as $table) {
                        if (!in_array($table, $tables)) {
                            $tables[] = $table;
                        }
                    }
                }
                // Also catch: $this->db->query('CREATE TABLE IF NOT EXISTS `table_name`')
                if (preg_match_all("/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`'\"]?(\w+)[`'\"]?/i", $content, $matches)) {
                    foreach ($matches[1] as $table) {
                        if (!in_array($table, $tables)) {
                            $tables[] = $table;
                        }
                    }
                }
            }
        }

        // 3. Scan all PHP files in the module for table references in models/config
        // Look for common CI patterns: $this->db->table('name'), from('name'), etc.
        $allPhpFiles = self::findPhpManifestFiles($modulePath);
        foreach ($allPhpFiles as $file) {
            $content = File::get($file);
            // Pattern: from('table_name') or $this->db->table('table_name')
            if (preg_match_all("/(?:->from|->table)\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
                foreach ($matches[1] as $table) {
                    // Skip CI internal tables and common non-module tables
                    if (in_array($table, ['users', 'sessions', 'password_resets', 'cache'])) {
                        continue;
                    }
                    // Only include tables that look like they belong to this module
                    $aliasPrefix = str_replace('-', '_', strtolower($moduleAlias));
                    if (str_starts_with($table, $aliasPrefix . '_') || str_starts_with($table, $aliasPrefix)) {
                        if (!in_array($table, $tables)) {
                            $tables[] = $table;
                        }
                    }
                }
            }
        }

        if (!empty($tables)) {
            $backupData = [];
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $backupData[$table] = DB::table($table)->get()->toArray();
                }
            }

            // Write backup JSON
            if (!empty($backupData)) {
                $backupDir = storage_path('app/backups/modules');
                File::ensureDirectoryExists($backupDir);
                $filename = strtolower($moduleAlias) . '_backup_' . time() . '.json';
                File::put("{$backupDir}/{$filename}", json_encode($backupData, JSON_PRETTY_PRINT));
            }

            // Roll back Laravel migrations if the directory exists
            $migrationsPath = "Modules/{$moduleAlias}/Database/Migrations";
            if (File::exists(base_path($migrationsPath))) {
                Artisan::call('migrate:rollback', [
                    '--path' => $migrationsPath,
                    '--realpath' => false,
                ]);
            }

            // Clean drop for any table left behind
            foreach ($tables as $table) {
                Schema::dropIfExists($table);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────────
    // Legacy CI Compatibility Symlinks
    // ──────────────────────────────────────────────────────────────────────────────

    /**
     * Create the legacy CodeIgniter-style symlink for a module.
     *
     * Old CI modules reference their files with:
     *   require('modules/hr_payroll/assets/js/...')
     * which assumes a root-relative path. We create:
     *   {base_path}/modules/{alias_underscored}/ → Modules/{alias}/
     * so that chdir(base_path()) + require('modules/...') works for ANY module.
     */
    public static function createLegacySymlink(string $alias): void
    {
        // No-op: Filesystem symlinks are no longer created because CICompatLayer,
        // PluginBridgeService, and Asset Streamer resolve aliases dynamically in memory.
    }

    /**
     * Remove legacy CI symlinks when a module is uninstalled.
     */
    public static function removeLegacySymlink(string $alias): void
    {
        $underscoreAlias = str_replace('-', '_', $alias);
        foreach ([$underscoreAlias, $alias] as $linkName) {
            $symlinkPath = base_path("Modules/{$linkName}");
            if (is_link($symlinkPath)) {
                @unlink($symlinkPath);
            }
        }
    }

    /**
     * Recursively register menus from menu.json tree.
     */
    public static function registerMenuNode(string $moduleId, array $node, ?int $parentId = null): void
    {
        $title = $node['title'] ?? null;
        $route = $node['route'] ?? '';
        if (!$title) {
            return;
        }

        $menuNode = ModuleMenu::create([
            'module_id' => $moduleId,
            'parent_id' => $parentId,
            'title' => self::formatMenuTitle($title),
            'route' => $route,
            'icon' => $node['icon'] ?? null,
            'permission' => $node['permission'] ?? null,
        ]);

        if (!empty($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                self::registerMenuNode($moduleId, $child, $menuNode->id);
            }
        }
    }

    /**
     * Convert a menu title key to a human-readable display name.
     * "hr_manage_employees" → "Manage Employees"
     * "hrp_reports" → "Reports"
     * "settings" → "Settings"
     */
    public static function formatMenuTitle(string $title): string
    {
        // Strip common module prefixes (e.g., "hr_", "hrp_", "gtsverify_")
        $cleaned = preg_replace('/^[a-z]{2,5}_/', '', $title);
        // If stripping left nothing meaningful, use the original
        if (empty(trim($cleaned, '_'))) {
            $cleaned = $title;
        }
        // Convert underscores/hyphens to spaces and title-case
        $human = ucwords(str_replace(['_', '-'], ' ', $cleaned));
        return $human;
    }

    /**
     * Recursively search for a file in a directory tree.
     */
    private static function findFileInDir(string $dir, string $filename): ?string
    {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..' || $object === '__MACOSX' || str_starts_with($object, '.')) {
                continue;
            }
            $path = "{$dir}/{$object}";
            if (is_dir($path)) {
                $res = self::findFileInDir($path, $filename);
                if ($res) {
                    return $res;
                }
            } elseif ($object === $filename) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Recursively search for all PHP files in a directory tree.
     */
    private static function findPhpManifestFiles(string $dir): array
    {
        $files = [];
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            $path = "{$dir}/{$object}";
            if (is_dir($path)) {
                $files = array_merge($files, self::findPhpManifestFiles($path));
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $files[] = $path;
            }
        }
        return $files;
    }

    /**
     * Recursively search for all ZIP files in a directory tree.
     */
    private static function findNestedZipFiles(string $dir): array
    {
        $files = [];
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            // Skip Apple Double resource fork files (macOS __MACOSX cruft)
            if (str_starts_with($object, '._')) {
                continue;
            }
            $path = "{$dir}/{$object}";
            if (is_dir($path)) {
                $files = array_merge($files, self::findNestedZipFiles($path));
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'zip') {
                $files[] = $path;
            }
        }
        return $files;
    }

    /**
     * Parse WordPress/iBridge style PHP comment headers from a PHP file.
     */
    public static function parsePhpHeaders(string $filePathOrContent, ?string $filePath = null): ?array
    {
        if (str_contains($filePathOrContent, "\n") || str_contains($filePathOrContent, "<?php")) {
            $content = $filePathOrContent;
        } elseif (is_file($filePathOrContent)) {
            $filePath = $filePathOrContent;
            $content = file_get_contents($filePathOrContent);
        } else {
            return null;
        }

        if (!$content) {
            return null;
        }

        // We only scan the first 8KB of the file for comments
        $content = substr($content, 0, 8192);

        $headers = [
            'name' => 'Module Name',
            'version' => 'Version',
            'description' => 'Description',
            'author' => 'Author',
            'minimum_core_version' => 'Requires at least',
        ];

        $info = [];
        $hasName = false;

        foreach ($headers as $key => $headerName) {
            if (preg_match('~^[ \t/*#]*' . preg_quote($headerName, '~') . ':\s*([^\r\n*]+)~mi', $content, $matches)) {
                $info[$key] = trim($matches[1]);
                if ($key === 'name') {
                    $hasName = true;
                }
            }
        }

        if (!$hasName) {
            return null;
        }

        // Generate alias from filename or module name
        if (!empty($filePath)) {
            $info['alias'] = \App\Services\ModuleValidator::normalizeAlias(pathinfo($filePath, PATHINFO_FILENAME));
        } else {
            $info['alias'] = \App\Services\ModuleValidator::normalizeAlias($info['name'] ?? 'module');
        }
        $info['name'] = $info['name'] ?? ucfirst($info['alias']);
        $info['version'] = $info['version'] ?? '1.0.0';
        $info['minimum_core_version'] = $info['minimum_core_version'] ?? '1.0.0';
        $info['depends'] = [];

        return $info;
    }

    /**
     * Run CodeIgniter legacy install.php and migrations using a Laravel compatibility layer mock.
     */
    public static function runLegacyInstallerAndMigrations(string $alias): void
    {
        $targetPath = base_path("Modules/{$alias}");

        // 1. Load the full CI compatibility layer
        require_once base_path('app/Services/CICompatLayer.php');
        if (!defined('BASEPATH')) {
            define('BASEPATH', true);
        }


        // Define $CI in local scope for install.php or migrations to reference
        $CI = get_instance();

        // 2. Pre-load module main entry file to register constants, hooks, and helpers
        $entryPhp = "{$targetPath}/{$alias}.php";
        if (File::exists($entryPhp)) {
            try {
                include_once $entryPhp;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Could not pre-load entry file [{$entryPhp}]: " . $e->getMessage());
            }
        } else {
            foreach (glob("{$targetPath}/*.php") as $rootPhp) {
                $bn = basename($rootPhp);
                if ($bn !== 'install.php' && $bn !== 'uninstall.php' && $bn !== 'deactivate.php') {
                    if (str_contains(file_get_contents($rootPhp), 'Module Name:')) {
                        try {
                            include_once $rootPhp;
                        } catch (\Throwable $e) {
                            // ignore
                        }
                        break;
                    }
                }
            }
        }

        // 3. Load helper files from helper directory to define custom helper functions
        $helpersDir = "{$targetPath}/helpers";
        if (is_dir($helpersDir)) {
            foreach (glob($helpersDir . '/*.php') as $helperFile) {
                try {
                    include_once $helperFile;
                } catch (\Exception $e) {
                    // Ignore load errors for helper functions
                }
            }
        }

        // 3. Run install.php
        $installScript = "{$targetPath}/install.php";
        if (File::exists($installScript)) {
            try {
                include_once $installScript;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed executing legacy install.php for plugin [{$alias}]: " . $e->getMessage());
            }
        }

        // 4. Scan and run CI migration files
        $migrationsDir = "{$targetPath}/migrations";
        if (is_dir($migrationsDir)) {
            $migrationFiles = glob($migrationsDir . '/*.php');
            sort($migrationFiles); // Ensure sorted order (e.g. 101, 102...)
            foreach ($migrationFiles as $migrationFile) {
                try {
                    $content = File::get($migrationFile);
                    
                    // Include the migration file to define the class
                    include_once $migrationFile;

                    // Parse the class name from file content (e.g. Migration_Version_101)
                    if (preg_match('/class\s+(Migration_\w+)/i', $content, $classMatches)) {
                        $className = $classMatches[1];
                        if (class_exists($className)) {
                            $migrationInstance = new $className();
                            if (method_exists($migrationInstance, 'up')) {
                                $migrationInstance->up();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed executing legacy migration [{$migrationFile}] for plugin [{$alias}]: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Repair a module: re-run migrations, re-register permissions and menus, publish assets.
     *
     * @throws Exception
     */
    public static function repair(string $id): Module
    {
        $module = Module::findOrFail($id);
        $modulePath = self::modulePath($module->alias, $module->name);

        if (!is_dir($modulePath)) {
            throw new Exception("Cannot repair module: Folder '{$module->alias}' does not exist.");
        }

        // Re-read permissions and menus from files
        $permissions = [];
        $permissionsPath = "{$modulePath}/permissions.json";
        if (File::exists($permissionsPath)) {
            $permissionsContent = File::get($permissionsPath);
            $permissions = json_decode($permissionsContent, true);
            $permissions = is_array($permissions) ? $permissions : [];
        }

        $menu = null;
        $menuPath = "{$modulePath}/menu.json";
        if (File::exists($menuPath)) {
            $menuContent = File::get($menuPath);
            $menu = json_decode($menuContent, true);
            $menu = is_array($menu) ? $menu : null;
        }

        DB::transaction(function () use ($module, $permissions, $menu) {
            // Re-run migrations
            $migrationsPath = "Modules/{$module->alias}/Database/Migrations";
            if (File::exists(base_path($migrationsPath))) {
                Artisan::call('migrate', [
                    '--path' => $migrationsPath,
                    '--realpath' => false,
                ]);
            }

            // Sync permissions and menus only if active
            if ($module->status === 'active') {
                // Delete old registered permissions and menus for this module
                \App\Models\ModuleMenu::where('module_id', $module->id)->delete();
                
                $permNames = \App\Models\ModulePermission::where('module_id', $module->id)->pluck('permission_name')->toArray();
                if (!empty($permNames)) {
                    $permissionIds = Permission::whereIn('name', $permNames)->pluck('id')->toArray();
                    if (!empty($permissionIds)) {
                        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
                        Permission::whereIn('id', $permissionIds)->delete();
                    }
                    \App\Models\ModulePermission::where('module_id', $module->id)->delete();
                }

                // Register permissions
                $adminRole = Role::where('slug', 'admin')->first();
                foreach ($permissions as $perm) {
                    $pName = $perm['key'] ?? $perm['name'] ?? null;
                    if ($pName) {
                        $pDesc = $perm['description'] ?? '';
                        $permission = Permission::firstOrCreate(
                            ['name' => $pName],
                            ['description' => $pDesc]
                        );
                        \App\Models\ModulePermission::firstOrCreate([
                            'module_id' => $module->id,
                            'permission_name' => $pName,
                        ]);
                        if ($adminRole) {
                            DB::table('role_permissions')->insertOrIgnore([
                                'role_id' => $adminRole->id,
                                'permission_id' => $permission->id,
                            ]);
                        }
                    }
                }

                // Register menus
                if ($menu) {
                    self::registerMenuNode($module->id, $menu);
                }
            }
        });

        // Flush settings cache
        ModuleSettingsService::flushSchemaCache($module);

        // Log the event
        \App\Models\ModuleEvent::create([
            'module_id' => $module->id,
            'module_alias' => $module->alias,
            'event_name' => 'ModuleRepaired',
            'payload' => [
                'name' => $module->name,
                'version' => $module->version,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return $module;
    }

    /**
     * Rollback a module: roll back its migrations and deactivate it.
     *
     * @throws Exception
     */
    public static function rollback(string $id): Module
    {
        $module = Module::findOrFail($id);

        DB::transaction(function () use ($module) {
            // Rollback database migrations
            $migrationsPath = "Modules/{$module->alias}/Database/Migrations";
            if (File::exists(base_path($migrationsPath))) {
                Artisan::call('migrate:rollback', [
                    '--path' => $migrationsPath,
                    '--realpath' => false,
                ]);
            }

            // Revert status to inactive
            if ($module->status === 'active') {
                self::deactivate($module->id);
            }
        });

        // Log the event
        \App\Models\ModuleEvent::create([
            'module_id' => $module->id,
            'module_alias' => $module->alias,
            'event_name' => 'ModuleRolledBack',
            'payload' => [
                'name' => $module->name,
                'version' => $module->version,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        $module->refresh();
        return $module;
    }

    /**
     * Auto-migrate legacy Vue files from skeleton/resources/js/views/module/
     * to resources/js/pages/ with snake_case names matching menu routes.
     */
    protected static function migrateLegacyVueFiles(string $alias): void
    {
        $modulePath = base_path("Modules/{$alias}");
        $legacyViewsDir = "{$modulePath}/skeleton/resources/js/views/module";
        $sdkPagesDir = "{$modulePath}/resources/js/pages";

        if (!File::exists($legacyViewsDir)) {
            return;
        }

        // Build route->permission mapping from menu.json
        $menuPath = "{$modulePath}/menu.json";
        $routeMap = []; // permission => route path
        if (File::exists($menuPath)) {
            $menuContent = File::get($menuPath);
            $menu = json_decode($menuContent, true);
            if (is_array($menu)) {
                self::collectRoutesFromMenu($menu, $routeMap);
            }
        }

        // Ensure target directory exists
        File::ensureDirectoryExists($sdkPagesDir);

        // Get all legacy Vue files
        $legacyFiles = File::glob("{$legacyViewsDir}/*.vue");
        if (empty($legacyFiles)) {
            return;
        }

        // Build a mapping from PascalCase component name to snake_case
        foreach ($legacyFiles as $legacyFile) {
            // Skip placeholder/migration files - read first 200 chars to check
            $preview = File::get($legacyFile);
            $preview = substr($preview, 0, 200);
            if (str_contains($preview, 'Manual Migration Required') || 
                str_contains($preview, '🚧 Manual Migration') ||
                str_contains($preview, 'migration-badge')) {
                continue; // Skip placeholder files, let SSO iframe handle them
            }

            $filename = basename($legacyFile, '.vue');
            // Convert PascalCase to snake_case: HrPayrollManageEmployees -> manage_employees
            // We strip common prefixes like HrPayroll, Gtsverify
            $snakeName = self::pascalToSnake($filename);
            $snakeName = self::stripModulePrefix($snakeName, $alias);

            // If we have a menu route that matches, use it
            $targetName = $snakeName;
            foreach ($routeMap as $permission => $route) {
                // Route is like "/manage_employees", extract "manage_employees"
                $routeName = ltrim($route, '/');
                if (str_contains($snakeName, $routeName) || str_contains($routeName, $snakeName)) {
                    $targetName = $routeName;
                    break;
                }
            }

            $targetPath = "{$sdkPagesDir}/{$targetName}.vue";

            // Copy only if target doesn't exist (don't overwrite SDK-native files)
            if (!File::exists($targetPath)) {
                File::copy($legacyFile, $targetPath);
            }
        }
    }

    /**
     * Recursively collect routes from menu tree.
     */
    protected static function collectRoutesFromMenu(array $menu, array &$routeMap): void
    {
        if (isset($menu['route']) && isset($menu['permission'])) {
            // Strip query string from route (e.g., "/setting?group=x" -> "/setting")
            $route = $menu['route'];
            $queryPos = strpos($route, '?');
            if ($queryPos !== false) {
                $route = substr($route, 0, $queryPos);
            }
            $routeMap[$menu['permission']] = $route;
        }
        if (!empty($menu['children']) && is_array($menu['children'])) {
            foreach ($menu['children'] as $child) {
                self::collectRoutesFromMenu($child, $routeMap);
            }
        }
    }

    /**
     * Convert PascalCase to snake_case.
     * HrPayrollManageEmployees -> hr_payroll_manage_employees
     */
    protected static function pascalToSnake(string $str): string
    {
        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $str));
    }

    /**
     * Strip common module prefix from snake_case name.
     * hr_payroll_manage_employees -> manage_employees
     * gtsverify_index -> index
     */
    protected static function stripModulePrefix(string $snakeName, string $alias): string
    {
        $prefix = str_replace('-', '_', $alias) . '_';
        if (str_starts_with($snakeName, $prefix)) {
            return substr($snakeName, strlen($prefix));
        }
        // Also try singular
        $singularPrefix = rtrim($prefix, 's') . '_';
        if (str_starts_with($snakeName, $singularPrefix)) {
            return substr($snakeName, strlen($singularPrefix));
        }
        return $snakeName;
    }
}
