<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Registries\PluginRegistry;
use Illuminate\Support\Facades\Cache;

/**
 * Class PluginCacheCommand
 * 
 * Artisan command caching plugin manifests and metadata configurations.
 */
class PluginCacheCommand extends Command
{
    protected $signature = 'plugin:cache';
    protected $description = 'Pre-compile and cache all plugin registries, configurations, and manifests';

    public function handle(): int
    {
        $this->info("Warming plugin discovery cache...");
        
        $registry = app(PluginRegistry::class);
        $plugins = $registry->getPlugins();

        Cache::put('plugin_discovery_manifest', $plugins, 86400);
        $this->info("Successfully cached " . count($plugins) . " plugin(s) registry records.");

        return Command::SUCCESS;
    }
}
