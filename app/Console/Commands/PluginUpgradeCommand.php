<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Upgrade\PluginUpgradeManager;
use App\Plugin\Registries\PluginRegistry;

/**
 * Class PluginUpgradeCommand
 * 
 * Artisan command executing sequential plugin upgrades.
 */
class PluginUpgradeCommand extends Command
{
    protected $signature = 'plugin:upgrade {plugin : Alias of the plugin} {target : New target version}';
    protected $description = 'Upgrade a plugin to a specific version running transition scripts';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $target = $this->argument('target');

        $registry = app(PluginRegistry::class);
        $upgradeManager = app(PluginUpgradeManager::class);

        $plugin = $registry->getPlugin($alias);
        if (!$plugin) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $this->info("Upgrading {$plugin->getName()} from {$plugin->getVersion()} to {$target}...");
        
        try {
            $upgradeManager->upgrade($plugin, $target);
            $this->info("Successfully upgraded plugin to version {$target}.");
        } catch (\Throwable $e) {
            $this->error("Upgrade failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
