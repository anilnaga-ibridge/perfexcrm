<?php

namespace App\Plugin\Upgrade;

use Illuminate\Support\Facades\File;
use Exception;

/**
 * Class PluginPackageManager
 * 
 * Verifies plugin package folders layout and maintains dependency
 * resolutions via plugin-lock.json.
 */
class PluginPackageManager
{
    /**
     * Validate the package directory layout format.
     * 
     * @throws Exception if validation fails.
     */
    public function validatePackageLayout(string $pluginPath): void
    {
        // Manifest must be present (supporting legacy module.json or enterprise plugin.json)
        $manifestFile = File::exists($pluginPath . '/plugin.json') ? '/plugin.json' : '/module.json';
        if (!File::exists($pluginPath . $manifestFile)) {
            throw new Exception("Validation Error: Missing plugin manifest file (plugin.json or module.json).");
        }

        // Must contain basic directories
        $requiredDirs = [
            'routes',
            'Providers',
        ];

        foreach ($requiredDirs as $dir) {
            if (!File::isDirectory($pluginPath . '/' . $dir)) {
                throw new Exception("Validation Error: Missing required directory '{$dir}'.");
            }
        }
    }

    /**
     * Rebuild and save the plugin-lock.json file.
     */
    public function generateLockFile(string $pluginPath, array $resolvedVersions): void
    {
        $lockFile = $pluginPath . '/plugin-lock.json';
        
        $lockData = [
            'sdk_version' => '3.0.0',
            'resolved_dependencies' => $resolvedVersions,
            'generated_at' => now()->toIso8601String(),
        ];

        File::put($lockFile, json_encode($lockData, JSON_PRETTY_PRINT));
    }
}
