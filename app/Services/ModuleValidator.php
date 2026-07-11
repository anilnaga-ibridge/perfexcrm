<?php

namespace App\Services;

use App\Models\Module;
use Exception;

class ModuleValidator
{
    /**
     * Canonicalize a module alias:
     *   - Lowercase
     *   - Replace any sequence of non-alphanumeric characters with a single hyphen
     *   - Strip leading/trailing hyphens
     */
    public static function normalizeAlias(string $alias): string
    {
        $alias = strtolower($alias);
        $alias = preg_replace('/[^a-z0-9]+/', '-', $alias);
        $alias = trim($alias, '-');
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
}
