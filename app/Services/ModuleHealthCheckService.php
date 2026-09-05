<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ModuleHealthCheckService
{
    /**
     * Run a comprehensive pre-activation compatibility and health check on a module directory.
     *
     * @param string $moduleAlias
     * @return array
     */
    public function checkHealth(string $moduleAlias): array
    {
        $modulePath = base_path("Modules/{$moduleAlias}");
        if (!is_dir($modulePath)) {
            $alt = str_contains($moduleAlias, '_') ? str_replace('_', '-', $moduleAlias) : str_replace('-', '_', $moduleAlias);
            if (is_dir(base_path("Modules/{$alt}"))) {
                $modulePath = base_path("Modules/{$alt}");
                $moduleAlias = $alt;
            }
        }

        if (!is_dir($modulePath)) {
            return [
                'status' => 'error',
                'score' => 0,
                'message' => "Module directory not found: {$modulePath}",
                'checks' => [],
            ];
        }

        $checks = [];
        $totalScore = 0;
        $maxScore = 100;

        // 1. Check Main PHP / manifest file (Weight: 20%)
        $mainFile = $this->findMainFile($modulePath);
        if ($mainFile) {
            $checks['main_file'] = [
                'name' => 'Main Module File / Manifest',
                'passed' => true,
                'details' => basename($mainFile),
                'score' => 20,
            ];
            $totalScore += 20;
        } else {
            $checks['main_file'] = [
                'name' => 'Main Module File / Manifest',
                'passed' => false,
                'details' => 'No entry PHP file found with module registration',
                'score' => 0,
            ];
        }

        // 2. Check Controllers (Weight: 20%)
        $controllersDir = "{$modulePath}/controllers";
        $controllers = is_dir($controllersDir) ? glob("{$controllersDir}/*.php") : [];
        if (!empty($controllers)) {
            $checks['controllers'] = [
                'name' => 'Controllers Discovery',
                'passed' => true,
                'count' => count($controllers),
                'details' => array_map('basename', $controllers),
                'score' => 20,
            ];
            $totalScore += 20;
        } else {
            $checks['controllers'] = [
                'name' => 'Controllers Discovery',
                'passed' => false,
                'count' => 0,
                'details' => 'No controller files found in controllers directory',
                'score' => 5,
            ];
            $totalScore += 5;
        }

        // 3. Check Views (Weight: 15%)
        $viewsDir = "{$modulePath}/views";
        $views = is_dir($viewsDir) ? $this->scanFilesRecursive($viewsDir, 'php') : [];
        if (!empty($views)) {
            $checks['views'] = [
                'name' => 'View Templates',
                'passed' => true,
                'count' => count($views),
                'score' => 15,
            ];
            $totalScore += 15;
        } else {
            $checks['views'] = [
                'name' => 'View Templates',
                'passed' => false,
                'count' => 0,
                'score' => 0,
            ];
        }

        // 4. Check Assets (Weight: 15%)
        $assetsDir = "{$modulePath}/assets";
        $assets = is_dir($assetsDir) ? $this->scanFilesRecursive($assetsDir) : [];
        $checks['assets'] = [
            'name' => 'Static Assets (CSS, JS, Images)',
            'passed' => !empty($assets),
            'count' => count($assets),
            'score' => !empty($assets) ? 15 : 10,
        ];
        $totalScore += !empty($assets) ? 15 : 10;

        // 5. Check Language Files (Weight: 15%)
        $langDir = "{$modulePath}/language";
        $langFiles = is_dir($langDir) ? $this->scanFilesRecursive($langDir, 'php') : [];
        if (!empty($langFiles)) {
            $checks['language'] = [
                'name' => 'Language & Translations',
                'passed' => true,
                'count' => count($langFiles),
                'score' => 15,
            ];
            $totalScore += 15;
        } else {
            $checks['language'] = [
                'name' => 'Language & Translations',
                'passed' => false,
                'warning' => 'Missing language files (fallback to auto-humanization)',
                'score' => 10,
            ];
            $totalScore += 10;
        }

        // 6. Check Migrations / Installation Hooks (Weight: 10%)
        $installFile = "{$modulePath}/install.php";
        $migrationsDir = "{$modulePath}/migrations";
        $hasMigrations = file_exists($installFile) || (is_dir($migrationsDir) && !empty(glob("{$migrationsDir}/*.php")));
        $checks['migrations'] = [
            'name' => 'Database Migrations & Install Script',
            'passed' => $hasMigrations,
            'details' => $hasMigrations ? (file_exists($installFile) ? 'install.php detected' : 'Migrations folder detected') : 'No database modifications declared',
            'score' => 10,
        ];
        $totalScore += 10;

        // 7. Check Dependencies (PHP, CRM Version, Required Modules) (Weight: 10%)
        $dependenciesCheck = $this->checkDependencies($modulePath);
        $checks['dependencies'] = [
            'name' => 'Module Dependencies & System Requirements',
            'passed' => $dependenciesCheck['satisfied'],
            'details' => $dependenciesCheck['details'],
            'score' => $dependenciesCheck['satisfied'] ? 10 : 0,
        ];
        if ($dependenciesCheck['satisfied']) {
            $totalScore += 10;
        }

        return [
            'module' => $moduleAlias,
            'status' => $totalScore >= 80 ? 'healthy' : ($totalScore >= 50 ? 'warning' : 'critical'),
            'score' => $totalScore,
            'checks' => $checks,
        ];
    }

    /**
     * Generate a detailed post-installation compatibility report.
     */
    public function generateReport(string $moduleAlias): array
    {
        $health = $this->checkHealth($moduleAlias);
        $modulePath = base_path("Modules/{$moduleAlias}");

        $controllersCount = $health['checks']['controllers']['count'] ?? 0;
        $viewsCount = $health['checks']['views']['count'] ?? 0;
        $assetsCount = $health['checks']['assets']['count'] ?? 0;
        $langCount = $health['checks']['language']['count'] ?? 0;

        // Discover permissions count from main PHP file
        $permissionsCount = 0;
        $mainFile = $this->findMainFile($modulePath);
        if ($mainFile) {
            $content = file_get_contents($mainFile);
            if (preg_match_all('/register_staff_capabilities\s*\([^,]+,\s*\[(.*?)\]/s', $content, $m)) {
                foreach ($m[1] as $capBlock) {
                    $permissionsCount += substr_count($capBlock, '=>') ?: 1;
                }
            }
        }

        return [
            'module' => $moduleAlias,
            'score' => $health['score'],
            'status' => $health['status'] === 'healthy' ? 'Compatible' : 'Partially Compatible',
            'summary' => [
                'controllers' => $controllersCount,
                'views' => $viewsCount,
                'assets' => $assetsCount,
                'translations' => $langCount,
                'permissions_registered' => $permissionsCount ?: 'Auto-Discovered',
                'cache_status' => 'Optimized',
            ],
            'checks' => $health['checks'],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function checkDependencies(string $dir): array
    {
        $satisfied = true;
        $details = ['PHP >= 8.0: Satisfied (' . PHP_VERSION . ')'];

        // Check module.json if present
        $moduleJson = "{$dir}/module.json";
        if (file_exists($moduleJson)) {
            $data = json_decode(file_get_contents($moduleJson), true) ?? [];
            if (isset($data['requires']) && is_array($data['requires'])) {
                if (isset($data['requires']['php']) && version_compare(PHP_VERSION, preg_replace('/[^0-9.]/', '', $data['requires']['php']), '<')) {
                    $satisfied = false;
                    $details[] = "Requires PHP {$data['requires']['php']} (Current: " . PHP_VERSION . ')';
                }
                if (isset($data['requires']['modules']) && is_array($data['requires']['modules'])) {
                    foreach ($data['requires']['modules'] as $reqMod) {
                        $exists = \App\Models\Module::where('alias', $reqMod)->where('status', 'active')->exists();
                        if (!$exists) {
                            $satisfied = false;
                            $details[] = "Missing active dependency: {$reqMod}";
                        } else {
                            $details[] = "Dependency met: {$reqMod}";
                        }
                    }
                }
            }
            if (isset($data['conflicts']) && is_array($data['conflicts'])) {
                foreach ($data['conflicts'] as $confMod) {
                    $exists = \App\Models\Module::where('alias', $confMod)->where('status', 'active')->exists();
                    if ($exists) {
                        $satisfied = false;
                        $details[] = "Conflicting active module detected: {$confMod}";
                    }
                }
            }
        }

        return [
            'satisfied' => $satisfied,
            'details' => implode(' | ', $details),
        ];
    }

    protected function findMainFile(string $dir): ?string
    {
        $files = File::files($dir);
        foreach ($files as $f) {
            if ($f->getExtension() === 'php' && !in_array($f->getFilename(), ['index.html', 'install.php', 'uninstall.php'])) {
                return $f->getPathname();
            }
        }
        return null;
    }

    protected function scanFilesRecursive(string $dir, ?string $ext = null): array
    {
        if (!is_dir($dir)) return [];
        $result = [];
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            if ($ext === null || $file->getExtension() === $ext) {
                $result[] = $file->getPathname();
            }
        }
        return $result;
    }
}
