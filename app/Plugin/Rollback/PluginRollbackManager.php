<?php

namespace App\Plugin\Rollback;

use App\Contracts\Plugins\PluginInterface;
use App\Plugin\Storage\PluginStorageManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class PluginRollbackManager
 * 
 * Takes runtime configuration and database settings snapshots,
 * and handles rolling back plugin states on upgrade failures.
 */
class PluginRollbackManager
{
    protected PluginStorageManager $storage;

    public function __construct(PluginStorageManager $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Create a backup snapshot of the plugin settings and configurations.
     */
    public function createSnapshot(PluginInterface $plugin): string
    {
        $alias = strtolower($plugin->getAlias());
        $tempDir = $this->storage->getTempPath($alias);
        $snapshotFile = $tempDir . '/rollback_snapshot_' . time() . '.json';

        $snapshotData = [
            'alias' => $plugin->getAlias(),
            'version' => $plugin->getVersion(),
            'timestamp' => time(),
            'settings' => [],
        ];

        // Backup module settings from db
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_settings')) {
                $snapshotData['settings'] = DB::table('module_settings')
                    ->where('module_alias', $alias)
                    ->get()
                    ->toArray();
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }

        File::put($snapshotFile, json_encode($snapshotData, JSON_PRETTY_PRINT));
        return $snapshotFile;
    }

    /**
     * Restore the plugin status from a snapshot file.
     */
    public function restoreFromSnapshot(PluginInterface $plugin, string $snapshotPath): void
    {
        if (!File::exists($snapshotPath)) {
            return;
        }

        $alias = strtolower($plugin->getAlias());
        $data = json_decode(File::get($snapshotPath), true);
        if (!$data || $data['alias'] !== $plugin->getAlias()) {
            return;
        }

        // Restore module settings to database
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_settings')) {
                // Delete current settings first
                DB::table('module_settings')->where('module_alias', $alias)->delete();

                // Re-insert previous settings
                foreach ($data['settings'] as $setting) {
                    DB::table('module_settings')->insert((array)$setting);
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
    }

    /**
     * Get snapshot execution history for a plugin.
     */
    public function getHistory(string $pluginAlias): array
    {
        $alias = strtolower($pluginAlias);
        $tempDir = $this->storage->getTempPath($alias);
        if (!File::isDirectory($tempDir)) {
            return [];
        }

        $files = File::glob($tempDir . '/rollback_snapshot_*.json');
        $history = [];

        foreach ($files as $file) {
            $data = json_decode(File::get($file), true);
            if ($data) {
                $history[] = [
                    'version' => $data['version'] ?? 'unknown',
                    'timestamp' => $data['timestamp'] ?? 0,
                    'file' => basename($file),
                ];
            }
        }

        return $history;
    }
}
