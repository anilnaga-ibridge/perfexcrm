<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Metadata\PluginMetadata;

/**
 * Class PluginInfoCommand
 * 
 * Artisan command displaying structural characteristics of a specific plugin.
 */
class PluginInfoCommand extends Command
{
    protected $signature = 'plugin:info {plugin : Alias of the plugin}';
    protected $description = 'Display full metadata and capability analysis for a plugin';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $meta = app(PluginMetadata::class)->get($alias);

        if (!$meta) {
            $this->error("Plugin '{$alias}' not found.");
            return Command::FAILURE;
        }

        $this->info("=================================================");
        $this->info("Plugin: {$meta['name']} (v{$meta['version']})");
        $this->info("=================================================");
        $this->line("  Alias:       {$meta['alias']}");
        $this->line("  Namespace:   {$meta['namespace']}");
        $this->line("  Path:        {$meta['path']}");
        $this->line("  Status:      " . strtoupper($meta['status']));
        $this->line("  Health:      " . strtoupper($meta['health']));

        $this->newLine();
        $this->comment("Capabilities Detected:");
        foreach ($meta['capabilities'] as $capKey => $value) {
            $status = is_array($value) ? (array_filter($value) ? '✔ YES' : '✘ NO') : ($value ? '✔ YES' : '✘ NO');
            $this->line(sprintf("  %-15s : %s", ucfirst($capKey), $status));
        }

        return Command::SUCCESS;
    }
}
