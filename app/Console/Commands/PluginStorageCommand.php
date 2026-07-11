<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Storage\PluginStorageManager;

/**
 * Class PluginStorageCommand
 * 
 * Artisan command auditing and listing isolated directory locations.
 */
class PluginStorageCommand extends Command
{
    protected $signature = 'plugin:storage {plugin : Alias of the plugin}';
    protected $description = 'Inspect and verify file path structures for a plugin';

    public function handle(): int
    {
        $alias = $this->argument('plugin');
        $storage = app(PluginStorageManager::class);

        $this->info("=================================================");
        $this->info("Plugin Storage Configuration: {$alias}");
        $this->info("=================================================");
        $this->line("  Storage Path: " . $storage->getStoragePath($alias));
        $this->line("  Cache Path:   " . $storage->getCachePath($alias));
        $this->line("  Log Path:     " . $storage->getLogPath($alias));
        $this->line("  Temp Path:    " . $storage->getTempPath($alias));

        return Command::SUCCESS;
    }
}
