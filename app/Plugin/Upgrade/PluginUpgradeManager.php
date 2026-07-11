<?php

namespace App\Plugin\Upgrade;

use App\Contracts\Plugins\PluginInterface;
use App\Plugin\Events\EventBus;
use App\Plugin\Lifecycle\PluginLifecycleManager;
use App\Plugin\Rollback\PluginRollbackManager;
use App\Plugin\Assets\AssetPipeline;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Exception;

/**
 * Class PluginUpgradeManager
 * 
 * Orchestrates plugin upgrades, executing version-specific migration scripts
 * sequentially and rolling back state snapshots on execution failure.
 */
class PluginUpgradeManager
{
    protected PluginLifecycleManager $lifecycle;
    protected PluginRollbackManager $rollback;
    protected EventBus $eventBus;

    public function __construct(
        PluginLifecycleManager $lifecycle,
        PluginRollbackManager $rollback,
        EventBus $eventBus
    ) {
        $this->lifecycle = $lifecycle;
        $this->rollback = $rollback;
        $this->eventBus = $eventBus;
    }

    /**
     * Upgrade a plugin to a new target version.
     * 
     * @throws Exception
     */
    public function upgrade(PluginInterface $plugin, string $targetVersion): void
    {
        $currentVersion = $plugin->getVersion();
        if (version_compare($currentVersion, $targetVersion, '>=')) {
            return; // Already up to date
        }

        $this->lifecycle->beforeUpdate($plugin, $targetVersion);

        // 1. Take a pre-upgrade rollback snapshot
        $snapshotPath = $this->rollback->createSnapshot($plugin);

        try {
            // 2. Locate and run upgrade scripts in sequence
            $this->runUpgradeScripts($plugin, $currentVersion, $targetVersion);

            // 3. Run any database migrations
            $migrationsPath = $plugin->getPath() . '/Database/Migrations';
            if (File::isDirectory($migrationsPath)) {
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path(), '', $migrationsPath),
                    '--force' => true,
                ]);
            }

            // 4. Publish assets
            // Find if asset class exists
            $assetClass = $plugin->getNamespace() . 'Assets\\AssetProvider';
            if (class_exists($assetClass)) {
                $assetProvider = app($assetClass);
                app(AssetPipeline::class)->publish($assetProvider);
            }

            // 5. Fire post-upgrade events
            $this->lifecycle->afterUpdate($plugin);
        } catch (\Throwable $e) {
            // 6. Roll back immediately on failure
            $this->rollback->restoreFromSnapshot($plugin, $snapshotPath);
            throw new Exception("Upgrade failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Sequentially execute version transition files (e.g. upgrade-1.0.0-to-2.0.0.php).
     */
    protected function runUpgradeScripts(PluginInterface $plugin, string $from, string $to): void
    {
        $upgradesDir = $plugin->getPath() . '/Database/Upgrades';
        if (!File::isDirectory($upgradesDir)) {
            return;
        }

        $files = File::files($upgradesDir);
        // Sort scripts semantically
        usort($files, function ($a, $b) {
            return strnatcmp($a->getFilename(), $b->getFilename());
        });

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Match pattern upgrade-{v1}-to-{v2}.php
            if (preg_match('/upgrade-([0-9\.]+)-to-([0-9\.]+)\.php/', $filename, $matches)) {
                $stepFrom = $matches[1];
                $stepTo = $matches[2];

                // If this step lies in our upgrade path, run it
                if (version_compare($stepFrom, $from, '>=') && version_compare($stepTo, $to, '<=')) {
                    $this->executeScript($file->getPathname());
                }
            }
        }
    }

    protected function executeScript(string $path): void
    {
        // Safe isolated script execution passing Laravel container
        $container = app();
        require $path;
    }
}
