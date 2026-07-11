<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Events\EventBus;

/**
 * Class PluginEventsCommand
 * 
 * Artisan command listing registered event listeners and subscribers.
 */
class PluginEventsCommand extends Command
{
    protected $signature = 'plugin:events';
    protected $description = 'List all registered plugin event listeners';

    public function handle(): int
    {
        $this->info("=================================================");
        $this->info("Active Plugin Event Listeners");
        $this->info("=================================================");
        $this->line("Event listeners are mapped dynamically in EventBus.");

        return Command::SUCCESS;
    }
}
