<?php

namespace App\Plugin\Storage;

use Illuminate\Support\Facades\File;

/**
 * Class PluginStorageManager
 * 
 * Auto-creates and governs the isolated folder hierarchy for plugins,
 * preventing cross-plugin file pollution and directory traversal.
 */
class PluginStorageManager
{
    /**
     * Get path to the plugin's storage space, auto-creating it.
     */
    public function getStoragePath(string $pluginAlias): string
    {
        $path = storage_path('plugins/' . strtolower($pluginAlias));
        $this->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Get path to the plugin's cache space, auto-creating it.
     */
    public function getCachePath(string $pluginAlias): string
    {
        $path = storage_path('framework/cache/plugins/' . strtolower($pluginAlias));
        $this->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Get path to the plugin's logs space, auto-creating it.
     */
    public function getLogPath(string $pluginAlias): string
    {
        $path = storage_path('logs/plugins/' . strtolower($pluginAlias));
        $this->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Get path to the plugin's temp space, auto-creating it.
     */
    public function getTempPath(string $pluginAlias): string
    {
        $path = storage_path('app/temp/plugins/' . strtolower($pluginAlias));
        $this->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Clean up all directories related to a plugin.
     */
    public function purge(string $pluginAlias): void
    {
        $alias = strtolower($pluginAlias);
        File::deleteDirectory(storage_path('plugins/' . $alias));
        File::deleteDirectory(storage_path('framework/cache/plugins/' . $alias));
        File::deleteDirectory(storage_path('logs/plugins/' . $alias));
        File::deleteDirectory(storage_path('app/temp/plugins/' . $alias));
    }

    /**
     * Helper to verify path safety and prevent directory traversal.
     */
    public function checkPathSafety(string $filePath, string $pluginAlias): bool
    {
        $root = realpath($this->getStoragePath($pluginAlias));
        $realFile = realpath($filePath);

        if ($root === false || $realFile === false) {
            return false;
        }

        return str_starts_with($realFile, $root);
    }

    /**
     * Initialize directory with appropriate permissions.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
            // Put a blank index.html to prevent folder indexing
            File::put($path . '/index.html', '');
        }
    }
}
