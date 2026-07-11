<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Class PluginClearCommand
 * 
 * Artisan command clearing all plugin runtime caches.
 */
class PluginClearCommand extends Command
{
    protected $signature = 'plugin:clear';
    protected $description = 'Clear all cached plugin structures and manifests';

    public function handle(): int
    {
        $this->info("Clearing plugin runtime caches...");
        Cache::forget('plugin_discovery_manifest');
        $this->info("Cache cleared successfully.");

        return Command::SUCCESS;
    }
}
