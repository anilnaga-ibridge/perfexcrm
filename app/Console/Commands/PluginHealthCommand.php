<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Health\HealthMonitor;
use App\Plugin\Registries\PluginRegistry;

/**
 * Class PluginHealthCommand
 * 
 * Artisan command auditing the health status of active plugins.
 */
class PluginHealthCommand extends Command
{
    protected $signature = 'plugin:health {plugin? : Alias of the plugin to audit}';
    protected $description = 'Audit the health of a specific plugin or all active plugins';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $registry = app(PluginRegistry::class);
        $healthMonitor = app(HealthMonitor::class);

        $plugins = [];
        if ($alias) {
            $plugin = $registry->getPlugin($alias);
            if ($plugin) {
                $plugins[] = $plugin;
            } else {
                $this->error("Plugin '{$alias}' not found.");
                return Command::FAILURE;
            }
        } else {
            $plugins = $registry->getPlugins();
        }

        foreach ($plugins as $plugin) {
            $report = $healthMonitor->checkPlugin($plugin->getAlias());
            $status = strtoupper($report['status']);
            $color = $report['status'] === 'healthy' ? 'info' : 'error';

            $this->line("Plugin: {$plugin->getName()} -> status: <$color>{$status}</$color>");
        }

        return Command::SUCCESS;
    }
}
