<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Registries\PluginRegistry;

/**
 * Class PluginReleaseCommand
 * 
 * Artisan command simulating cloud deployment of plugin ZIP releases.
 */
class PluginReleaseCommand extends Command
{
    protected $signature = 'plugin:release {plugin : The alias of the plugin}';
    protected $description = 'Package and release a plugin to the repository feeds';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $registry = app(PluginRegistry::class);

        $plugin = $registry->getPlugin($alias);
        if (!$plugin) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $this->info("Initiating release pipeline for: {$plugin->getName()}...");

        // 1. Pack plugin
        $this->call('plugin:package', ['plugin' => $alias]);

        // 2. Sign plugin
        $this->call('plugin:sign', ['plugin' => $alias]);

        // 3. Verify plugin
        $this->call('plugin:verify', ['plugin' => $alias]);

        $this->info("Release Pipeline complete. Package is ready for publication.");
        return Command::SUCCESS;
    }
}
