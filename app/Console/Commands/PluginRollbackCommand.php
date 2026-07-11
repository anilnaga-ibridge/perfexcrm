<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Rollback\PluginRollbackManager;
use App\Plugin\Registries\PluginRegistry;

/**
 * Class PluginRollbackCommand
 * 
 * Artisan command restoring plugin states from snapshot files.
 */
class PluginRollbackCommand extends Command
{
    protected $signature = 'plugin:rollback {plugin : Alias of the plugin} {file : Filename of the snapshot}';
    protected $description = 'Roll back a plugin state from a previous backup snapshot';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $file = $this->argument('file');

        $registry = app(PluginRegistry::class);
        $rollbackManager = app(PluginRollbackManager::class);

        $plugin = $registry->getPlugin($alias);
        if (!$plugin) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $snapshotPath = storage_path('app/temp/plugins/' . strtolower($alias) . '/' . $file);
        
        $this->info("Restoring {$plugin->getName()} from snapshot {$file}...");
        
        try {
            $rollbackManager->restoreFromSnapshot($plugin, $snapshotPath);
            $this->info("Successfully restored plugin state.");
        } catch (\Throwable $e) {
            $this->error("Rollback failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
