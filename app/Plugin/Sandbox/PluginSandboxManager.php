<?php

namespace App\Plugin\Sandbox;

use App\Plugin\Storage\PluginStorageManager;
use Exception;

/**
 * Class PluginSandboxManager
 * 
 * Enforces file access isolation, preventing traversal outside
 * the plugin's allowed storage/logs/cache directories.
 */
class PluginSandboxManager
{
    protected PluginStorageManager $storage;

    public function __construct(PluginStorageManager $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Ensure the path is inside the plugin's allowed storage directory.
     * 
     * @throws Exception if traversal or violation is detected.
     */
    public function validatePath(string $path, string $pluginAlias): void
    {
        $allowedRoots = [
            $this->storage->getStoragePath($pluginAlias),
            $this->storage->getCachePath($pluginAlias),
            $this->storage->getLogPath($pluginAlias),
            $this->storage->getTempPath($pluginAlias),
        ];

        $resolvedPath = realpath($path) ?: $path;

        $isSafe = false;
        foreach ($allowedRoots as $root) {
            $resolvedRoot = realpath($root) ?: $root;
            if (str_starts_with($resolvedPath, $resolvedRoot)) {
                $isSafe = true;
                break;
            }
        }

        if (!$isSafe) {
            throw new Exception("Sandbox Violation: Path '{$path}' is outside the authorized storage bounds for plugin '{$pluginAlias}'.");
        }
    }
}
