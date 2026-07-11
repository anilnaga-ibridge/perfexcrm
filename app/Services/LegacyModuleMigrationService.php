<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LegacyModuleMigrationService
{
    protected string $modulePath;
    protected string $moduleAlias;
    protected array $analysis = [];

    public function migrate(string $modulePath, string $outputPath): array
    {
        $this->modulePath = rtrim($modulePath, '/');
        $this->analysis = $this->analyze();

        File::ensureDirectoryExists($outputPath);

        $this->generateModuleJson($outputPath);
        $this->generateMenuJson($outputPath);
        $this->generatePermissionsJson($outputPath);
        $this->generateSkeletonControllers($outputPath);
        $this->generateSkeletonVuePages($outputPath);

        $report = $this->generateReport();

        File::put($outputPath . '/migration-report.md', $report);

        return $this->analysis;
    }

    public function analyze(): array
    {
        $mainFile = $this->findMainFile();
        $content = File::get($mainFile);

        return [
            'metadata'       => $this->extractMetadata($content),
            'permissions'    => $this->extractPermissions($content),
            'menus'          => $this->extractMenus($content),
            'controllers'    => $this->listControllers(),
            'views'          => $this->listViews(),
            'helpers'        => $this->listHelpers(),
            'models'         => $this->listModels(),
            'libraries'      => $this->listLibraries(),
            'hooks'          => $this->extractHooks($content),
            'unsupported'    => $this->findUnsupportedApis($content),
            'migration_date' => now()->toDateTimeString(),
            'source_module'  => basename($this->modulePath),
        ];
    }

    // ─── File discovery ─────────────────────────────────────────────────

    protected function findMainFile(): string
    {
        $files = File::files($this->modulePath);
        foreach ($files as $file) {
            $name = $file->getFilename();
            if ($name !== 'index.html' && $name !== 'install.php' && $file->getExtension() === 'php') {
                return $file->getPathname();
            }
        }
        throw new \RuntimeException("No main PHP file found in {$this->modulePath}");
    }

    protected function detectAlias(): string
    {
        $dirName = basename($this->modulePath);
        // Convert snake_case or kebab-case to the alias convention
        return Str::slug(str_replace('_', '-', $dirName));
    }

    // ─── Metadata extraction ─────────────────────────────────────────────

    protected function extractMetadata(string $content): array
    {
        $metadata = [
            'name'           => '',
            'description'    => '',
            'version'        => '',
            'author'         => '',
            'author_uri'     => '',
            'requires_at_least' => '',
            'module_name_constant' => null,
            'original_dir'   => basename($this->modulePath),
        ];

        // Extract PHP docblock header
        if (preg_match('/\/\*(.*?)\*\//s', $content, $headerMatch)) {
            $header = trim($headerMatch[1]);
            $lines = explode("\n", $header);
            foreach ($lines as $line) {
                $line = preg_replace('/^\s*\*\s?/', '', $line);
                $line = trim($line);
                if (preg_match('/^Module\s*Name:\s*(.+)$/i', $line, $m)) {
                    $metadata['name'] = trim($m[1]);
                } elseif (preg_match('/^Description:\s*(.+)$/i', $line, $m)) {
                    $metadata['description'] = trim($m[1]);
                } elseif (preg_match('/^Version:\s*(.+)$/i', $line, $m)) {
                    $metadata['version'] = trim($m[1]);
                } elseif (preg_match('/^Author:\s*(.+)$/i', $line, $m)) {
                    $metadata['author'] = trim($m[1]);
                } elseif (preg_match('/^Author\s*URI:\s*(.+)$/i', $line, $m)) {
                    $metadata['author_uri'] = trim($m[1]);
                } elseif (preg_match('/^Requires\s+at\s+least:\s*(.+)$/i', $line, $m)) {
                    $metadata['requires_at_least'] = trim($m[1]);
                }
            }
        }

        // Extract module name constant (e.g., define('HR_PAYROLL_MODULE_NAME', 'hr_payroll'))
        if (preg_match("/define\s*\(\s*['\"](\w+_MODULE_NAME)['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i", $content, $m)) {
            $metadata['module_name_constant'] = [
                'constant' => $m[1],
                'value'    => $m[2],
            ];
        }

        if (empty($metadata['name'])) {
            $metadata['name'] = Str::title(str_replace(['-', '_'], ' ', basename($this->modulePath)));
        }

        return $metadata;
    }

    // ─── Permission extraction ───────────────────────────────────────────

    protected function extractPermissions(string $content): array
    {
        $permissions = [];
        $variables = $this->extractCapabilityVariables($content);

        $pattern = '/register_staff_capabilities\s*\(\s*' .
            "['\"]([^'\"]+)['\"]\s*,\s*" .    // permission key
            '\$(\w+)\s*,\s*' .                 // variable name
            "(?:get_instance\s*\(\s*\)\s*->\s*lang\s*\(\s*['\"]([^'\"]+)['\"]\s*\)|_l\s*\(\s*['\"]([^'\"]+)['\"]\s*\))" .
            '\s*\)/sx';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = $match[1];
                $varName = $match[2];
                $label = $match[3] ?: $match[4];

                $caps = $variables[$varName] ?? [];

                $permissions[] = [
                    'key'          => $key,
                    'label'        => $label,
                    'capabilities' => $caps,
                    'auto_migrated' => true,
                ];
            }
        }

        // Fallback: simpler regex without label extraction
        if (empty($permissions)) {
            preg_match_all(
                "/register_staff_capabilities\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)/s",
                $content, $matches, PREG_SET_ORDER
            );
            foreach ($matches as $match) {
                $key = $match[1];
                $varPart = trim($match[2]);
                $label = trim($match[3]);
                $label = preg_replace("/^_l\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", '$1', $label);
                $label = preg_replace("/get_instance\s*\(\s*\)\s*->\s*lang\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", '$1', $label);

                $varName = ltrim($varPart, '$');
                $caps = $variables[$varName] ?? [];

                $permissions[] = [
                    'key'          => $key,
                    'label'        => $label,
                    'capabilities' => $caps,
                    'auto_migrated' => true,
                ];
            }
        }

        return $permissions;
    }

    protected function extractCapabilityVariables(string $content): array
    {
        $variables = [];

        // Match patterns like:
        // $capabilities_3['capabilities'] = [ ... ];
        // $dashboard['capabilities'] = [ ... ];
        $pattern = '/(\$(\w+))\s*\[\s*[\'"]capabilities[\'"]\s*\]\s*=\s*\[(.*?)\]\s*;/s';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $varName = $match[2];
                $capsBody = $match[3];
                $caps = [];

                // Extract individual capability keys
                if (preg_match_all("/['\"](\w+)['\"]\s*=>/s", $capsBody, $capMatches)) {
                    $caps = $capMatches[1];
                }

                $variables[$varName] = $caps;
            }
        }

        return $variables;
    }

    // ─── Menu extraction ─────────────────────────────────────────────────

    protected function extractMenus(string $content): array
    {
        $menus = [
            'root'     => null,
            'children' => [],
        ];

        // Extract root menu item: $CI->app_menu->add_sidebar_menu_item(...)
        $rootPattern = '/\$CI\s*->\s*app_menu\s*->\s*add_sidebar_menu_item\s*\(\s*' .
            "['\"]([^'\"]+)['\"]\s*,\s*\[(.*?)\]\s*\)/s";

        if (preg_match($rootPattern, $content, $m)) {
            $rootKey = $m[1];
            $rootBody = $m[2];

            $menus['root'] = [
                'key'      => $rootKey,
                'name'     => $this->extractArrayValue($rootBody, 'name'),
                'icon'     => $this->extractArrayValue($rootBody, 'icon'),
                'position' => $this->extractArrayValue($rootBody, 'position'),
            ];
        }

        // Fallback: simpler pattern if the above fails
        if (!$menus['root']) {
            $simplePattern = "/add_sidebar_menu_item\s*\(\s*'([^']+)'\s*,\s*\[(.*?)\]\s*\)/s";
            if (preg_match($simplePattern, $content, $m)) {
                $rootBody = $m[2];
                $menus['root'] = [
                    'key'      => $m[1],
                    'name'     => $this->extractArrayValue($rootBody, 'name'),
                    'icon'     => $this->extractArrayValue($rootBody, 'icon'),
                    'position' => $this->extractArrayValue($rootBody, 'position'),
                ];
            }
        }

        // Extract children: $CI->app_menu->add_sidebar_children_item(...)
        $childPattern = '/\$CI\s*->\s*app_menu\s*->\s*add_sidebar_children_item\s*\(\s*' .
            "['\"]([^'\"]+)['\"]\s*,\s*\[(.*?)\]\s*\)/s";

        if (preg_match_all($childPattern, $content, $childMatches, PREG_SET_ORDER)) {
            foreach ($childMatches as $cm) {
                $parentKey = $cm[1];
                $childBody = $cm[2];

                $href = $this->extractArrayValue($childBody, 'href');
                $route = $this->convertHrefToRoute($href);

                $menus['children'][] = [
                    'parent_key' => $parentKey,
                    'slug'       => $this->extractArrayValue($childBody, 'slug'),
                    'name'       => $this->extractArrayValue($childBody, 'name'),
                    'icon'       => $this->extractArrayValue($childBody, 'icon'),
                    'position'   => $this->extractArrayValue($childBody, 'position'),
                    'href'       => $href,
                    'route'      => $route,
                ];
            }
        }

        // Fallback: simpler pattern
        if (empty($menus['children'])) {
            $simplePattern = "/add_sidebar_children_item\s*\(\s*'([^']+)'\s*,\s*\[(.*?)\]\s*\)/s";
            if (preg_match_all($simplePattern, $content, $childMatches, PREG_SET_ORDER)) {
                foreach ($childMatches as $cm) {
                    $parentKey = $cm[1];
                    $childBody = $cm[2];

                    $href = $this->extractArrayValue($childBody, 'href');
                    $route = $this->convertHrefToRoute($href);

                    $menus['children'][] = [
                        'parent_key' => $parentKey,
                        'slug'       => $this->extractArrayValue($childBody, 'slug'),
                        'name'       => $this->extractArrayValue($childBody, 'name'),
                        'icon'       => $this->extractArrayValue($childBody, 'icon'),
                        'position'   => $this->extractArrayValue($childBody, 'position'),
                        'href'       => $href,
                        'route'      => $route,
                    ];
                }
            }
        }

        return $menus;
    }

    protected function extractArrayValue(string $body, string $key): ?string
    {
        // Match 'key' => 'value' or "key" => "value"
        if (preg_match("/['\"]{$key}['\"]\s*=>\s*['\"]([^'\"]+)['\"]/s", $body, $m)) {
            return $m[1];
        }

        // Match 'key' => _l('value')
        if (preg_match("/['\"]{$key}['\"]\s*=>\s*_l\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/s", $body, $m)) {
            return $m[1];
        }

        // Match 'key' => admin_url('value')
        if (preg_match("/['\"]{$key}['\"]\s*=>\s*admin_url\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/s", $body, $m)) {
            return $m[1];
        }

        return null;
    }

    protected function convertHrefToRoute(?string $href): ?string
    {
        if (!$href) return null;

        // admin_url returns a path like 'hr_payroll/manage_employees'
        // or 'hr_payroll/setting?group=income_tax_rates'
        // Strip the controller prefix to get the pure route, preserving query strings
        if (str_contains($href, '/')) {
            $parts = explode('/', $href, 2);
            $suffix = end($parts);
            return '/' . $suffix;
        }

        return '/' . $href;
    }

    // ─── Controller analysis ─────────────────────────────────────────────

    protected function listControllers(): array
    {
        $controllerDir = $this->modulePath . '/controllers';
        $controllers = [];

        if (!is_dir($controllerDir)) {
            return $controllers;
        }

        foreach (File::files($controllerDir) as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = File::get($file->getPathname());
            $className = '';
            $extends = '';
            $methods = [];

            // Extract class name and parent
            if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/s', $content, $m)) {
                $className = $m[1];
                $extends = $m[2];
            }

            // Extract public methods
            preg_match_all('/public\s+function\s+(\w+)\s*\(/s', $content, $methodMatches);
            $methods = $methodMatches[1] ?? [];

            // Remove constructor from methods list
            $methods = array_values(array_filter($methods, fn($m) => $m !== '__construct'));

            $controllers[] = [
                'file'       => $file->getFilename(),
                'class'      => $className,
                'extends'    => $extends,
                'methods'    => $methods,
                'needs_migration' => true,
                'migration_status' => 'manual',
            ];
        }

        return $controllers;
    }

    // ─── View analysis ───────────────────────────────────────────────────

    protected function listViews(): array
    {
        $viewDir = $this->modulePath . '/views';
        $views = [];

        if (!is_dir($viewDir)) {
            return $views;
        }

        $files = File::allFiles($viewDir);
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') continue;

            $relativePath = Str::after($file->getPathname(), $viewDir . '/');
            $views[] = [
                'path'             => $relativePath,
                'size'             => $file->getSize(),
                'needs_migration'  => true,
                'migration_status' => 'manual',
            ];
        }

        return $views;
    }

    // ─── Helper / Model / Library analysis ───────────────────────────────

    protected function listHelpers(): array
    {
        $helperDir = $this->modulePath . '/helpers';
        $helpers = [];

        if (!is_dir($helperDir)) {
            return $helpers;
        }

        foreach (File::files($helperDir) as $file) {
            if ($file->getExtension() === 'php') {
                $helpers[] = [
                    'file'    => $file->getFilename(),
                    'size'    => $file->getSize(),
                    'needs_migration' => true,
                ];
            }
        }

        return $helpers;
    }

    protected function listModels(): array
    {
        $modelDir = $this->modulePath . '/models';
        $models = [];

        if (!is_dir($modelDir)) {
            return $models;
        }

        foreach (File::files($modelDir) as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getPathname());
                $className = '';
                $extends = '';

                if (preg_match('/class\s+(\w+)\s+extends\s+(\w+)/s', $content, $m)) {
                    $className = $m[1];
                    $extends = $m[2];
                }

                $models[] = [
                    'file'    => $file->getFilename(),
                    'class'   => $className,
                    'extends' => $extends,
                    'size'    => $file->getSize(),
                    'needs_migration' => true,
                ];
            }
        }

        return $models;
    }

    protected function listLibraries(): array
    {
        $libDir = $this->modulePath . '/libraries';
        $libraries = [];

        if (!is_dir($libDir)) {
            return $libraries;
        }

        foreach (File::files($libDir) as $file) {
            if ($file->getExtension() === 'php') {
                $libraries[] = [
                    'file'    => $file->getFilename(),
                    'size'    => $file->getSize(),
                    'needs_migration' => true,
                ];
            }
        }

        return $libraries;
    }

    // ─── Hook extraction ─────────────────────────────────────────────────

    protected function extractHooks(string $content): array
    {
        $hooks = [];

        preg_match_all(
            "/hooks\s*\(\)\s*->\s*add_action\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]/s",
            $content, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $hooks[] = [
                'hook'     => $m[1],
                'callback' => $m[2],
                'type'     => 'action',
            ];
        }

        preg_match_all(
            "/hooks\s*\(\)\s*->\s*add_filter\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]/s",
            $content, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $hooks[] = [
                'hook'     => $m[1],
                'callback' => $m[2],
                'type'     => 'filter',
            ];
        }

        return $hooks;
    }

    // ─── Unsupported API detection ───────────────────────────────────────

    protected function findUnsupportedApis(string $content): array
    {
        $unsupported = [];

        $patterns = [
            'CodeIgniter Loader'      => '/\$this\s*->\s*load\s*->/',
            'CodeIgniter Database'    => '/\$this\s*->\s*db\s*->/',
            'CodeIgniter Input'       => '/\$this\s*->\s*input\s*->/',
            '$CI global'              => '/\$CI\s*->/',
            'get_instance()'          => '/get_instance\s*\(/',
            'hooks() helper'          => '/hooks\s*\(\)/',
            '_l() translation'         => '/_l\s*\(/',
            'admin_url()'             => '/admin_url\s*\(/',
            'module_dir_path()'       => '/module_dir_path\s*\(/',
            'module_dir_url()'        => '/module_dir_url\s*\(/',
            'has_permission()'        => '/has_permission\s*\(/',
            'register_staff_capabilities()' => '/register_staff_capabilities\s*\(/',
            'register_activation_hook()' => '/register_activation_hook\s*\(/',
            'register_language_files()' => '/register_language_files\s*\(/',
            'register_merge_fields()' => '/register_merge_fields\s*\(/',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $unsupported[] = [
                    'api'     => $name,
                    'count'   => count($matches[0]),
                    'status'  => $this->getUnsupportedStatus($name),
                ];
            }
        }

        return $unsupported;
    }

    protected function getUnsupportedStatus(string $api): string
    {
        $autoMigrated = [
            '_l() translation',
            'admin_url()',
            'has_permission()',
            'register_staff_capabilities()',
        ];

        return in_array($api, $autoMigrated) ? 'auto-detectable' : 'manual';
    }

    // ─── Manifest generation ─────────────────────────────────────────────

    protected function generateModuleJson(string $outputPath): void
    {
        $meta = $this->analysis['metadata'];
        $alias = $this->detectAlias();

        $manifest = [
            'name'              => $meta['name'],
            'alias'             => $alias,
            'version'           => $meta['version'] ?: '1.0.0',
            'minimum_core_version' => $meta['requires_at_least'] ?: '1.0.0',
            'description'       => $meta['description'],
            'author'            => $meta['author'],
            'author_uri'        => $meta['author_uri'],
            'depends'           => [],
            'settings_route'    => null,
            '_migration_note'   => 'Auto-generated by Legacy Module Migration Tool from ' . $meta['original_dir'],
        ];

        File::put(
            $outputPath . '/module.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function generateMenuJson(string $outputPath): void
    {
        $menus = $this->analysis['menus'];
        $rootName = $menus['root']['name'] ?? $this->analysis['metadata']['name'];

        // Map permission keys from extracted permissions
        $permissionMap = [];
        foreach ($this->analysis['permissions'] as $perm) {
            $permissionMap[$perm['key']] = true;
        }

        $menu = [
            'title'      => $rootName,
            'route'      => '',
            'icon'       => $menus['root']['icon'] ?? 'modules',
            'permission' => null,
            'children'   => [],
        ];

        foreach ($menus['children'] as $child) {
            $route = $child['route'];
            $permKey = $this->findPermissionKeyForRoute($route, $child['name']);

            $menu['children'][] = [
                'title'      => $child['name'],
                'route'      => $route ?? '/' . Str::slug($child['name']),
                'permission' => $permKey,
            ];
        }

        File::put(
            $outputPath . '/menu.json',
            json_encode($menu, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function findPermissionKeyForRoute(?string $route, ?string $name): ?string
    {
        if (!$route) return null;

        // Strip query params and use the path portion only
        $path = parse_url($route, PHP_URL_PATH) ?? $route;
        $routeBase = trim($path, '/');

        // Common prefix mapping — sorted by specificity (longest first)
        $prefixMap = [
            'manage_employees'        => 'hrp_employee',
            'manage_attendance'       => 'hrp_attendance',
            'manage_commissions'      => 'hrp_commission',
            'manage_deductions'       => 'hrp_deduction',
            'manage_bonus'            => 'hrp_bonus_kpi',
            'manage_insurances'       => 'hrp_insurrance',
            'payslip_templates_manage' => 'hrp_payslip_template',
            'payslip_templates'       => 'hrp_payslip_template',
            'payslip_manage'          => 'hrp_payslip',
            'income_taxs_manage'      => 'hrp_income_tax',
            'income_tax'              => 'hrp_income_tax',
            'reports'                 => 'hrp_report',
            'settings'                => 'hrp_setting',
            'setting'                 => 'hrp_setting',
        ];

        // Sort by key length descending so more specific keys match first
        uksort($prefixMap, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($prefixMap as $key => $perm) {
            if (str_contains($routeBase, $key)) {
                foreach ($this->analysis['permissions'] as $p) {
                    if ($p['key'] === $perm) {
                        return $perm;
                    }
                }
            }
        }

        // Fall back: try to match permission key fragments in the path
        $slug = str_replace(['_', '-'], '', $routeBase);
        foreach ($this->analysis['permissions'] as $p) {
            $permSlug = str_replace(['_', '-'], '', $p['key']);
            if (str_contains($slug, $permSlug) || str_contains($permSlug, $slug)) {
                return $p['key'];
            }
        }

        return null;
    }

    protected function generatePermissionsJson(string $outputPath): void
    {
        $permissions = [];

        foreach ($this->analysis['permissions'] as $perm) {
            $caps = $perm['capabilities'];

            $mapped = [];
            foreach ($caps as $cap) {
                $mapped[$cap] = true;
            }

            // Default capabilities if none found
            if (empty($mapped)) {
                $mapped = [
                    'view'   => true,
                    'create' => true,
                    'edit'   => true,
                    'delete' => true,
                ];
            }

            $permissions[] = [
                'key'          => $perm['key'],
                'label'        => $perm['label'],
                'capabilities' => $mapped,
            ];
        }

        File::put(
            $outputPath . '/permissions.json',
            json_encode($permissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    // ─── Skeleton generation ─────────────────────────────────────────────

    protected function generateSkeletonControllers(string $outputPath): void
    {
        $controllerDir = $outputPath . '/skeleton/app/Http/Controllers/Module';
        File::ensureDirectoryExists($controllerDir);

        foreach ($this->analysis['controllers'] as $ctrl) {
            $className = $ctrl['class'];
            $methods = $ctrl['methods'];

            $stub = $this->buildControllerStub($className, $methods, $ctrl['extends']);

            File::put(
                $controllerDir . '/' . $className . '.php',
                $stub
            );
        }
    }

    protected function buildControllerStub(string $className, array $methods, string $extends): string
    {
        $methodStubs = '';
        foreach ($methods as $method) {
            $methodStubs .= <<<PHP

    /**
     * // TODO(Migration) Pending
     *
     * Legacy Controller:
     *   {$className}.php (extends {$extends})
     *
     * Original Method:
     *   {$method}()
     *
     * Original View:
     *   views/...
     *
     * Unsupported APIs:
     *   - \$this->load->...
     *   - \$CI->...
     *   - hooks()->...
     *
     * Migration Status:
     *   Pending — manual implementation required
     *
     * Original Code (unreachable):
     *   \$this->load->model(...);
     *   \$CI->db->get(...);
     *   \$this->load->view(...);
     */
    public function {$method}(Request \$request)
    {
        // TODO: Implement migrated logic from {$className}::{$method}()
        return response()->json([
            'success' => true,
            'message' => 'Not yet migrated — see TODO(Migration) above',
        ]);
    }

PHP;
        }

        $namespace = 'App\Http\Controllers\Module';
        $date = now()->toDateString();

        return <<<PHP
<?php

namespace {$namespace};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Auto-generated skeleton — {$date}.
 *
 * 🚧 Manual migration required.
 * Each method contains a TODO(Migration) block with legacy context.
 */
class {$className} extends Controller
{
{$methodStubs}
}

PHP;
    }

    protected function generateSkeletonVuePages(string $outputPath): void
    {
        $vueDir = $outputPath . '/skeleton/resources/js/views/module';
        File::ensureDirectoryExists($vueDir);

        foreach ($this->analysis['controllers'] as $ctrl) {
            foreach ($ctrl['methods'] as $method) {
                $componentName = Str::studly($ctrl['class'] . '_' . $method);
                $slug = Str::kebab($method);

                $stub = <<<VUE
<template>
  <div class="module-page">
    <h1>{$ctrl['class']}::{$method}()</h1>
    <p class="migration-badge">🚧 Manual Migration Required</p>

    <div class="migration-context">
      <table>
        <tr><td>Original controller</td><td><code>{$ctrl['class']}.php</code> extends <code>{$ctrl['extends']}</code></td></tr>
        <tr><td>Original method</td><td><code>{$method}()</code></td></tr>
        <tr><td>Original view</td><td><code>views/{$slug}.php</code></td></tr>
        <tr><td>Status</td><td><strong>Pending</strong></td></tr>
      </table>
    </div>

    <a-spin :spinning="loading">
      <!-- TODO(Migration): Replace with migrated content -->
      <a-empty description="Not yet migrated" />
    </a-spin>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  name: '{$componentName}',
  setup() {
    const loading = ref(false);

    // TODO(Migration): Fetch data from migrated API endpoint
    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/module/{$slug}');
        // Handle response
      } catch (err) {
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    return {
      loading,
      fetchData,
    };
  },
};
</script>

<style scoped>
.module-page { padding: 24px; }
.migration-badge {
  display: inline-block;
  background: #fef3cd;
  color: #856404;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 16px;
}
.migration-context {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 24px;
  font-size: 14px;
}
.migration-context table { width: 100%; }
.migration-context td { padding: 4px 8px; }
.migration-context td:first-child {
  color: #64748b;
  width: 160px;
}
</style>
VUE;

                File::put(
                    $vueDir . '/' . $componentName . '.vue',
                    $stub
                );
            }
        }
    }

    // ─── Scoring & estimation ────────────────────────────────────────────

    protected function calculateCompatibilityScore(): array
    {
        $weights = [
            'permissions' => 10,
            'menus'       => 10,
            'controllers' => 30,
            'views'       => 25,
            'models'      => 10,
            'helpers'     => 5,
            'hooks'       => 5,
            'unsupported' => 5,
        ];

        $scores = [];
        $totalWeight = array_sum($weights);
        $weightedScore = 0;

        // Permissions — fully auto-migrated
        $pct = min(100, count($this->analysis['permissions']) > 0 ? 100 : 0);
        $scores['permissions'] = ['label' => 'Permissions', 'score' => $pct, 'weight' => $weights['permissions']];
        $weightedScore += $pct * $weights['permissions'];

        // Menu items — fully auto-migrated
        $pct = min(100, count($this->analysis['menus']['children']) > 0 ? 100 : 0);
        $scores['menus'] = ['label' => 'Menus', 'score' => $pct, 'weight' => $weights['menus']];
        $weightedScore += $pct * $weights['menus'];

        // Controllers — skeleton only
        $ctrlCount = count($this->analysis['controllers']);
        // Each controller has skeleton generated = 10% progress per controller
        $ctrlPct = $ctrlCount > 0 ? 10 : 100;
        $scores['controllers'] = ['label' => 'Controllers', 'score' => $ctrlPct, 'weight' => $weights['controllers']];
        $weightedScore += $ctrlPct * $weights['controllers'];

        // Views — skeleton Vue pages only
        $viewCount = count($this->analysis['views']);
        $viewPct = $viewCount > 0 ? 5 : 100;
        $scores['views'] = ['label' => 'Views', 'score' => $viewPct, 'weight' => $weights['views']];
        $weightedScore += $viewPct * $weights['views'];

        // Models — no auto-migration possible
        $modelCount = count($this->analysis['models']);
        $modelPct = $modelCount > 0 ? 0 : 100;
        $scores['models'] = ['label' => 'Models', 'score' => $modelPct, 'weight' => $weights['models']];
        $weightedScore += $modelPct * $weights['models'];

        // Helpers — skeleton only (none generated currently)
        $helperCount = count($this->analysis['helpers']);
        $helperPct = $helperCount > 0 ? 0 : 100;
        $scores['helpers'] = ['label' => 'Helpers', 'score' => $helperPct, 'weight' => $weights['helpers']];
        $weightedScore += $helperPct * $weights['helpers'];

        // Hooks — pattern detected, needs manual port
        $hookCount = count($this->analysis['hooks']);
        $hookPct = $hookCount > 0 ? 0 : 100;
        $scores['hooks'] = ['label' => 'Hooks / Events', 'score' => $hookPct, 'weight' => $weights['hooks']];
        $weightedScore += $hookPct * $weights['hooks'];

        // Unsupported APIs — penalty
        $unsupportedCount = count($this->analysis['unsupported']);
        $unsupportedPct = max(0, 100 - ($unsupportedCount * 8));
        $scores['unsupported'] = ['label' => 'CI APIs', 'score' => $unsupportedPct, 'weight' => $weights['unsupported']];
        $weightedScore += $unsupportedPct * $weights['unsupported'];

        $overall = round($weightedScore / $totalWeight);

        return [
            'overall' => $overall,
            'details' => $scores,
            'total_weight' => $totalWeight,
        ];
    }

    protected function estimateEffort(): array
    {
        $a = $this->analysis;

        // Hours per item type
        $hours = [
            'permissions' => count($a['permissions']) * 0.1,
            'menus'       => count($a['menus']['children']) * 0.1,
            'controllers' => count($a['controllers']) * 2.0,
            'views'       => count($a['views']) * 0.5,
            'models'      => count($a['models']) * 1.5,
            'helpers'     => count($a['helpers']) * 0.5,
            'hooks'       => count($a['hooks']) * 0.3,
        ];

        $totalHours = array_sum($hours);
        $easy = $hours['permissions'] + $hours['menus'];
        $medium = $hours['hooks'] + $hours['helpers'] + $hours['models'];
        $hard = $hours['controllers'] + $hours['views'];

        return [
            'total_hours_low'    => round($totalHours * 0.8),
            'total_hours_high'   => round($totalHours * 1.3),
            'easy_hours'         => round($easy),
            'medium_hours'       => round($medium),
            'hard_hours'         => round($hard),
            'details'            => $hours,
        ];
    }

    protected function findBlockingIssues(): array
    {
        $blockers = [];

        foreach ($this->analysis['unsupported'] as $u) {
            if ($u['status'] === 'manual') {
                $blockers[] = [
                    'issue' => $u['api'],
                    'count' => $u['count'],
                    'impact' => $this->getBlockerImpact($u['api']),
                    'suggestion' => $this->getBlockerSuggestion($u['api']),
                ];
            }
        }

        // Additional structural blockers
        if (!empty($this->analysis['hooks'])) {
            $blockers[] = [
                'issue' => 'Legacy hooks() system',
                'count' => count($this->analysis['hooks']),
                'impact' => 'high',
                'suggestion' => 'Replace with Laravel event system or remove if no longer needed',
            ];
        }

        if (!empty($this->analysis['models'])) {
            $blockers[] = [
                'issue' => 'CodeIgniter models (CI database abstraction)',
                'count' => count($this->analysis['models']),
                'impact' => 'high',
                'suggestion' => 'Rewrite as Eloquent models',
            ];
        }

        if (count($this->analysis['views']) > 20) {
            $blockers[] = [
                'issue' => 'Large number of PHP views (' . count($this->analysis['views']) . ')',
                'count' => count($this->analysis['views']),
                'impact' => 'medium',
                'suggestion' => 'Port to Vue components — see skeleton/ directory',
            ];
        }

        return $blockers;
    }

    protected function getBlockerImpact(string $api): string
    {
        $high = [
            'CodeIgniter Loader',
            'CodeIgniter Database',
            '$CI global',
            'get_instance()',
            'hooks() helper',
            'CodeIgniter Input',
        ];
        return in_array($api, $high) ? 'high' : 'medium';
    }

    protected function getBlockerSuggestion(string $api): string
    {
        $suggestions = [
            'CodeIgniter Loader'       => 'Use Laravel dependency injection or facades',
            'CodeIgniter Database'     => 'Use Eloquent ORM or DB facade',
            'CodeIgniter Input'        => 'Use Laravel Request object',
            '$CI global'               => 'Inject dependencies via constructor',
            'get_instance()'           => 'Inject via Laravel service container',
            'hooks() helper'           => 'Use Laravel events or middleware',
            '_l() translation'         => 'Preserved — configure Laravel localization',
            'admin_url()'              => 'Use vue-router links or named routes',
            'module_dir_path()'        => 'Use Laravel storage or resource paths',
            'module_dir_url()'         => 'Use asset() or Storage facade',
            'has_permission()'         => 'Preserved via permission system',
            'register_staff_capabilities()' => 'Migrated to permissions.json',
            'register_activation_hook()' => 'Handled by activate/deactivate in ModuleManager',
            'register_language_files()' => 'Use Laravel lang files',
            'register_merge_fields()'  => 'Requires reimplementation',
        ];
        return $suggestions[$api] ?? 'Manual review required';
    }

    protected function progressBar(int $pct, int $width = 20): string
    {
        $filled = round($pct / 100 * $width);
        $empty  = $width - $filled;
        return str_repeat('█', $filled) . str_repeat('░', $empty) . " {$pct}%";
    }

    // ─── Report generation ───────────────────────────────────────────────

    public function generateReport(): string
    {
        $a = $this->analysis;
        $meta = $a['metadata'];
        $score = $this->calculateCompatibilityScore();
        $effort = $this->estimateEffort();
        $blockers = $this->findBlockingIssues();

        $report = [];
        $report[] = '# Legacy Module Migration Report';
        $report[] = '';
        $report[] = "**Module:** {$meta['name']}";
        $report[] = "**Source:** {$meta['original_dir']}";
        $report[] = "**Version:** {$meta['version']}";
        $report[] = "**Author:** {$meta['author']}";
        $report[] = "**Migrated on:** {$a['migration_date']}";
        $report[] = '';

        // ── Compatibility Score ──────────────────────────────────────────
        $report[] = '## Compatibility Score';
        $report[] = '';
        $report[] = $this->progressBar($score['overall'], 30);
        $report[] = '';
        $report[] = '| Component | Score |';
        $report[] = '|-----------|-------|';
        foreach ($score['details'] as $key => $d) {
            $bar = $this->progressBar($d['score'], 12);
            $report[] = "| {$d['label']} | {$bar} |";
        }
        $report[] = '';

        // ── Migration Summary ────────────────────────────────────────────
        $totalItems = 0;
        $autoItems = 0;
        $manualItems = 0;

        $report[] = '## Migration Summary';
        $report[] = '';
        $report[] = '| Category | Total | Auto-migrated | Manual Required |';
        $report[] = '|----------|-------|---------------|-----------------|';

        $permTotal = count($a['permissions']);
        $permAuto = count(array_filter($a['permissions'], fn($p) => $p['auto_migrated']));
        $permManual = $permTotal - $permAuto;
        $report[] = "| Permissions | {$permTotal} | {$permAuto} | {$permManual} |";
        $totalItems += $permTotal; $autoItems += $permAuto; $manualItems += $permManual;

        $menuTotal = count($a['menus']['children']);
        $menuAuto = $menuTotal;
        $menuManual = 0;
        $report[] = "| Menu Items | {$menuTotal} | {$menuAuto} | {$menuManual} |";
        $totalItems += $menuTotal; $autoItems += $menuAuto;

        $ctrlTotal = count($a['controllers']);
        $report[] = "| Controllers | {$ctrlTotal} | 0 | {$ctrlTotal} |";
        $totalItems += $ctrlTotal; $manualItems += $ctrlTotal;

        $viewTotal = count($a['views']);
        $report[] = "| Views | {$viewTotal} | 0 | {$viewTotal} |";
        $totalItems += $viewTotal; $manualItems += $viewTotal;

        $helperTotal = count($a['helpers']);
        $report[] = "| Helpers | {$helperTotal} | 0 | {$helperTotal} |";
        $totalItems += $helperTotal; $manualItems += $helperTotal;

        $modelTotal = count($a['models']);
        $report[] = "| Models | {$modelTotal} | 0 | {$modelTotal} |";
        $totalItems += $modelTotal; $manualItems += $modelTotal;

        $libraryTotal = count($a['libraries']);
        $report[] = "| Libraries | {$libraryTotal} | 0 | {$libraryTotal} |";
        $totalItems += $libraryTotal; $manualItems += $libraryTotal;

        $report[] = '';
        $report[] = "**Total:** {$totalItems} items, **{$autoItems}** auto-migrated, **{$manualItems}** require manual migration.";
        $report[] = '';

        // ── Estimated Effort ─────────────────────────────────────────────
        $report[] = '## Estimated Migration Effort';
        $report[] = '';
        $report[] = "**Developer time:** {$effort['total_hours_low']} – {$effort['total_hours_high']} hours";
        $report[] = '';
        $report[] = '| Difficulty | Hours | Items |';
        $report[] = '|------------|-------|-------|';
        $report[] = "| 🟢 Easy     | {$effort['easy_hours']}h | Permissions, Menus |";
        $report[] = "| 🟡 Medium   | {$effort['medium_hours']}h | Hooks, Helpers, Models |";
        $report[] = "| 🔴 Hard     | {$effort['hard_hours']}h | Controllers, Views |";
        $report[] = '';
        $report[] = "🟢 Easy — {$this->progressBar(100, 10)}";
        $report[] = "🟡 Medium — {$this->progressBar($effort['medium_hours'] ? 100 : 0, 10)}";
        $report[] = "🔴 Hard — {$this->progressBar(100, 10)}";
        $report[] = '';

        // ── Blocking Issues ──────────────────────────────────────────────
        if (!empty($blockers)) {
            $report[] = '## Blocking Issues';
            $report[] = '';
            $report[] = '| Issue | Occurrences | Impact | Suggestion |';
            $report[] = '|-------|-------------|--------|------------|';
            foreach ($blockers as $b) {
                $icon = $b['impact'] === 'high' ? '🔴' : '🟡';
                $report[] = "| {$icon} {$b['issue']} | {$b['count']} | {$b['impact']} | {$b['suggestion']} |";
            }
            $report[] = '';
        }

        // ── Generated Files ──────────────────────────────────────────────
        $report[] = '## Generated Files';
        $report[] = '';
        $report[] = '- `module.json` — Module manifest (✅ auto)';
        $report[] = '- `menu.json` — Sidebar menu definition (✅ auto)';
        $report[] = '- `permissions.json` — Permission definitions (✅ auto)';
        $report[] = '- `skeleton/app/Http/Controllers/Module/*.php` — Skeleton Laravel controllers (📝 skeleton)';
        $report[] = '- `skeleton/resources/js/views/module/*.vue` — Skeleton Vue pages (📝 skeleton)';
        $report[] = '';

        // ── Extracted Metadata ───────────────────────────────────────────
        $report[] = '## Extracted Metadata';
        $report[] = '';
        $report[] = '```json';
        $report[] = json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $report[] = '```';
        $report[] = '';

        // ── Permissions ──────────────────────────────────────────────────
        $report[] = '## Extracted Permissions';
        $report[] = '';
        foreach ($a['permissions'] as $perm) {
            $status = $perm['auto_migrated'] ? '✅ Auto-migrated' : '⚠️ Manual';
            $report[] = "- `{$perm['key']}` ({$perm['label']}) — {$status}";
        }
        $report[] = '';

        // ── Menu Items ───────────────────────────────────────────────────
        $report[] = '## Extracted Menu Items';
        $report[] = '';
        if ($a['menus']['root']) {
            $r = $a['menus']['root'];
            $report[] = "- **Root:** `{$r['key']}` → {$r['name']}";
        }
        foreach ($a['menus']['children'] as $child) {
            $report[] = "  - `{$child['slug']}` → {$child['name']} (route: {$child['route']})";
        }
        $report[] = '';

        // ── Controllers ──────────────────────────────────────────────────
        if (!empty($a['controllers'])) {
            $report[] = '## Controllers — Manual Migration Required';
            $report[] = '';
            foreach ($a['controllers'] as $ctrl) {
                $report[] = "### {$ctrl['class']} (extends {$ctrl['extends']})";
                $report[] = '';
                $report[] = "File: `{$ctrl['file']}`";
                $report[] = '';
                foreach ($ctrl['methods'] as $method) {
                    $report[] = "- `{$method}()`";
                }
                $report[] = '';
                $report[] = 'Skeleton generated at: `skeleton/app/Http/Controllers/Module/' . $ctrl['class'] . '.php`';
                $report[] = '';
            }
        }

        // ── Views ────────────────────────────────────────────────────────
        if (!empty($a['views'])) {
            $report[] = '## Views — Manual Migration Required';
            $report[] = '';
            foreach ($a['views'] as $view) {
                $report[] = "- `{$view['path']}` ({$view['size']} bytes)";
            }
            $report[] = '';
        }

        // ── Vue Pages Generated ──────────────────────────────────────────
        $vueCount = 0;
        foreach ($a['controllers'] as $ctrl) {
            $vueCount += count($ctrl['methods']);
        }
        if ($vueCount > 0) {
            $report[] = "## Vue Pages Generated ({$vueCount})";
            $report[] = '';
            foreach ($a['controllers'] as $ctrl) {
                foreach ($ctrl['methods'] as $method) {
                    $name = Str::studly($ctrl['class'] . '_' . $method);
                    $report[] = "- `{$name}.vue` ← {$ctrl['class']}::{$method}()";
                }
            }
            $report[] = '';
        }

        // ── Hooks ────────────────────────────────────────────────────────
        if (!empty($a['hooks'])) {
            $report[] = '## Hooks / Events';
            $report[] = '';
            $report[] = '| Hook | Callback | Type | Status |';
            $report[] = '|------|----------|------|--------|';
            foreach ($a['hooks'] as $hook) {
                $report[] = "| `{$hook['hook']}` | `{$hook['callback']}` | {$hook['type']} | ⚠️ Manual |";
            }
            $report[] = '';
        }

        // ── Unsupported APIs ─────────────────────────────────────────────
        if (!empty($a['unsupported'])) {
            $report[] = '## Unsupported Legacy APIs Detected';
            $report[] = '';
            $report[] = '| API | Occurrences | Status |';
            $report[] = '|-----|-------------|--------|';
            foreach ($a['unsupported'] as $u) {
                $icon = $u['status'] === 'auto-detectable' ? '✅' : '⚠️';
                $report[] = "| {$u['api']} | {$u['count']} | {$icon} {$u['status']} |";
            }
            $report[] = '';
        }

        // ── Next Steps ───────────────────────────────────────────────────
        $report[] = '## Next Steps';
        $report[] = '';
        $report[] = '1. Review `module.json`, `menu.json`, and `permissions.json` and adjust as needed.';
        $report[] = '2. Implement each skeleton controller in `skeleton/app/Http/Controllers/Module/`.';
        $report[] = '3. Implement each skeleton Vue page in `skeleton/resources/js/views/module/`.';
        $report[] = '4. Create API routes in `routes/api.php` for each controller method.';
        $report[] = '5. Register Vue page components in the router.';
        $report[] = '6. Port views (PHP/CI) to Vue template syntax.';
        $report[] = '7. Replace `_l()` calls with `i18n` or inline text.';
        $report[] = '8. Replace `admin_url()` with Vue Router links.';
        $report[] = '9. Replace CodeIgniter database queries with Eloquent models.';
        $report[] = '';

        return implode("\n", $report);
    }

}
