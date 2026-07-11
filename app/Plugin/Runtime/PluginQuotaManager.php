<?php

namespace App\Plugin\Runtime;

use App\Plugin\Storage\PluginStorageManager;
use Illuminate\Support\Facades\File;
use Exception;

/**
 * Class PluginQuotaManager
 * 
 * Enforces resource limit thresholds (memory, execution time, and storage space) per plugin.
 */
class PluginQuotaManager
{
    protected PluginStorageManager $storage;

    // Hard-coded default limits (can be loaded via configuration manager)
    protected array $limits = [
        'memory_limit' => 134217728, // 128MB
        'time_limit' => 30.0,       // 30 seconds
        'storage_limit' => 104857600, // 100MB
    ];

    public function __construct(PluginStorageManager $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Set a custom limit override.
     */
    public function setLimit(string $key, mixed $value): void
    {
        $this->limits[$key] = $value;
    }

    /**
     * Check if a plugin exceeds its allocated disk storage footprint.
     * 
     * @throws Exception if quota limit exceeded.
     */
    public function checkStorageQuota(string $pluginAlias): void
    {
        $storagePath = $this->storage->getStoragePath($pluginAlias);
        $currentSize = $this->getDirectorySize($storagePath);

        if ($currentSize > $this->limits['storage_limit']) {
            throw new Exception("Storage Quota Exceeded: Plugin '{$pluginAlias}' consumes {$currentSize} bytes (limit is {$this->limits['storage_limit']}).");
        }
    }

    /**
     * Get consolidated report of resources consumed by a plugin.
     */
    public function getUsageReport(string $pluginAlias): array
    {
        $storagePath = $this->storage->getStoragePath($pluginAlias);
        $cachePath = $this->storage->getCachePath($pluginAlias);

        return [
            'alias' => $pluginAlias,
            'storage_size' => $this->getDirectorySize($storagePath),
            'cache_size' => $this->getDirectorySize($cachePath),
            'limits' => $this->limits,
        ];
    }

    /**
     * Calculate directory file sizes.
     */
    protected function getDirectorySize(string $path): int
    {
        if (!File::isDirectory($path)) {
            return 0;
        }

        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
}
