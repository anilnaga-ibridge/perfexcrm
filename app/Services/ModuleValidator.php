<?php

namespace App\Services;

use App\Models\Module;
use Exception;

class ModuleValidator
{
    /**
     * Canonicalize a module alias:
     *   - Lowercase
     *   - Replace any sequence of non-alphanumeric/non-underscore characters with an underscore
     *   - Strip leading/trailing underscores
     */
    public static function normalizeAlias(string $alias): string
    {
        $alias = strtolower($alias);
        $alias = preg_replace('/[^a-z0-9_]+/', '_', $alias);
        $alias = trim($alias, '_');
        return $alias;
    }

    /**
     * Validate the module manifest content.
     *
     * @throws Exception
     */
    public static function validateManifest(array &$info, ?string $currentId = null): void
    {
        $requiredKeys = ['name', 'alias'];

        foreach ($requiredKeys as $key) {
            if (empty($info[$key])) {
                throw new Exception("Invalid manifest: Missing required field '{$key}'.");
            }
        }

        // Normalize alias format (lowercase letters, numbers, hyphens)
        $info['alias'] = self::normalizeAlias($info['alias']);

        // Ensure alias is unique
        $query = Module::where('alias', $info['alias']);
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }
        if ($query->exists()) {
            throw new Exception("Invalid manifest: A module with the alias '{$info['alias']}' is already registered.");
        }

        // Validate and normalize version format to semantic versioning
        if (empty($info['version'])) {
            $info['version'] = '1.0.0';
        } else {
            if (preg_match('/^(\d+)(?:\.(\d+))?(?:\.(\d+))?/', $info['version'], $matches)) {
                $major = $matches[1];
                $minor = $matches[2] ?? '0';
                $patch = $matches[3] ?? '0';
                $info['version'] = "{$major}.{$minor}.{$patch}";
            } else {
                $info['version'] = '1.0.0';
            }
        }

        if (empty($info['minimum_core_version'])) {
            $info['minimum_core_version'] = '1.0.0';
        }
    }

    /**
     * Validate the file structure and health of the module.
     *
     * @throws Exception
     */
    public static function validateHealth(string $moduleAlias, ?string $moduleName = null): void
    {
        $modulePath = base_path("Modules/{$moduleAlias}");

        if (!is_dir($modulePath)) {
            // Legacy fallback: check name-based directory
            if ($moduleName !== null) {
                $legacyPath = base_path("Modules/{$moduleName}");
                if (is_dir($legacyPath)) {
                    return;
                }
            }
            throw new Exception("Integrity check failed: Module folder '{$moduleAlias}' does not exist.");
        }
    }

    /**
     * Analyze module compatibility and return an itemized checklist with score (0-100%).
     */
    public static function analyzeCompatibility(string $modulePath): array
    {
        if (!is_dir($modulePath)) {
            return [
                'score' => 0,
                'status' => 'failed',
                'error' => "Module directory not found: {$modulePath}",
                'checks' => [],
            ];
        }

        $checks = [];
        $points = 0;
        $totalPoints = 8;

        // 1. Entry Point Check (module.json or {alias}.php with iBridge headers)
        $hasManifest = file_exists("{$modulePath}/module.json");
        $alias = basename($modulePath);
        $hasPhpEntry = file_exists("{$modulePath}/{$alias}.php");
        if (!$hasPhpEntry) {
            // Check any PHP file in root with Module Name header
            $rootPhp = glob("{$modulePath}/*.php");
            foreach ($rootPhp as $rf) {
                $content = file_get_contents($rf, false, null, 0, 4096);
                if (stripos($content, 'Module Name:') !== false) {
                    $hasPhpEntry = true;
                    break;
                }
            }
        }
        $entryValid = $hasManifest || $hasPhpEntry;
        $checks['entry_point'] = [
            'label' => 'Module Manifest / Entry Point',
            'passed' => $entryValid,
            'details' => $hasManifest ? 'Found module.json' : ($hasPhpEntry ? 'Found native PHP module entry header' : 'Missing module.json and PHP entry file'),
        ];
        if ($entryValid) $points++;

        // 2. Controllers Check
        $controllersDir = "{$modulePath}/controllers";
        $controllers = is_dir($controllersDir) ? glob("{$controllersDir}/*.php") : [];
        $hasControllers = count($controllers) > 0;
        $checks['controllers'] = [
            'label' => 'Controllers',
            'passed' => $hasControllers,
            'details' => $hasControllers ? count($controllers) . ' controller(s) discovered' : 'No controllers/ directory or PHP controllers found',
        ];
        if ($hasControllers) $points++;

        // 3. Models Check
        $modelsDir = "{$modulePath}/models";
        $models = is_dir($modelsDir) ? glob("{$modelsDir}/*.php") : [];
        $hasModels = count($models) > 0;
        $checks['models'] = [
            'label' => 'Models',
            'passed' => $hasModels,
            'details' => $hasModels ? count($models) . ' model(s) discovered' : 'No models/ directory or PHP models found',
        ];
        if ($hasModels) $points++;

        // 4. Views Check
        $viewsDir = "{$modulePath}/views";
        $hasViews = is_dir($viewsDir);
        $checks['views'] = [
            'label' => 'Views',
            'passed' => $hasViews,
            'details' => $hasViews ? 'Views directory present' : 'No views/ directory found',
        ];
        if ($hasViews) $points++;

        // 5. Assets Check
        $assetsDir = "{$modulePath}/assets";
        $hasAssets = is_dir($assetsDir);
        $checks['assets'] = [
            'label' => 'Assets (CSS/JS/Fonts/Images)',
            'passed' => $hasAssets,
            'details' => $hasAssets ? 'Assets directory present for automatic publishing' : 'No assets/ directory (optional)',
        ];
        // Assets are optional, so if not present it still passes or is neutral
        $checks['assets']['passed'] = true;
        $points++;

        // 6. Languages Check
        $langDir = "{$modulePath}/language";
        $hasLang = is_dir($langDir);
        $checks['languages'] = [
            'label' => 'Languages / Translations',
            'passed' => true,
            'details' => $hasLang ? 'Languages directory present' : 'No language/ directory (optional)',
        ];
        $points++;

        // 7. Migrations / Database Check
        $hasInstall = file_exists("{$modulePath}/install.php");
        $hasMigrations = is_dir("{$modulePath}/migrations") || is_dir("{$modulePath}/Database/Migrations");
        $checks['database'] = [
            'label' => 'Database Migrations / Installer',
            'passed' => true,
            'details' => $hasInstall ? 'Found install.php' : ($hasMigrations ? 'Found migrations directory' : 'No schema installer (optional)'),
        ];
        $points++;

        // 8. Hooks Check
        $entryContent = $hasPhpEntry && file_exists("{$modulePath}/{$alias}.php") ? file_get_contents("{$modulePath}/{$alias}.php") : '';
        $usesHooks = str_contains($entryContent, 'hooks()') || str_contains($entryContent, 'add_action') || str_contains($entryContent, 'add_filter');
        $checks['hooks'] = [
            'label' => 'Hooks & Actions Registration',
            'passed' => true,
            'details' => $usesHooks ? 'Registers hooks/actions' : 'No hooks detected (optional)',
        ];
        $points++;

        $score = round(($points / $totalPoints) * 100);

        return [
            'score' => $score,
            'status' => $score >= 80 ? 'compatible' : ($score >= 50 ? 'warning' : 'incompatible'),
            'checks' => $checks,
        ];
    }
}
