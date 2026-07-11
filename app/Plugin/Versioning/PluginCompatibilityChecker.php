<?php

namespace App\Plugin\Versioning;

use App\Contracts\Plugins\PluginInterface;
use App\Plugin\Versioning\VersionManager;
use Illuminate\Support\Facades\App;
use Exception;

/**
 * Class PluginCompatibilityChecker
 * 
 * Audits runtime environment constraints including PHP, Laravel core version,
 * and SDK compliance levels.
 */
class PluginCompatibilityChecker
{
    protected VersionManager $versionManager;

    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    /**
     * Check if the runtime environment satisfies the plugin compatibility limits.
     * 
     * @throws Exception if compatibility check fails.
     */
    public function check(PluginInterface $plugin): bool
    {
        $manifest = $this->getManifest($plugin);
        
        // 1. Check PHP Version
        $requiredPhp = $manifest['php'] ?? '8.2.0';
        if (version_compare(PHP_VERSION, $requiredPhp, '<')) {
            throw new Exception("Incompatibility: Plugin requires PHP version >= {$requiredPhp}. Current version is " . PHP_VERSION);
        }

        // 2. Check Laravel Version
        $laravelVer = App::version();
        $requiredLaravel = $manifest['laravel'] ?? '11.0.0';
        if (version_compare($laravelVer, $requiredLaravel, '<')) {
            throw new Exception("Incompatibility: Plugin requires Laravel version >= {$requiredLaravel}. Current version is {$laravelVer}");
        }

        // 3. Check SDK Version
        $sdkVer = $manifest['sdk'] ?? '1.0.0';
        // Assume current SDK version is 3.0.0
        if (version_compare('3.0.0', $sdkVer, '<')) {
            throw new Exception("Incompatibility: Plugin requires SDK version >= {$sdkVer}. Current SDK version is 3.0.0");
        }

        return true;
    }

    /**
     * Parse manifest to extract variables.
     */
    protected function getManifest(PluginInterface $plugin): array
    {
        $path = $plugin->getPath() . '/module.json';
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return [];
    }
}
