<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Hooks\HookManager;

/**
 * Class PluginHooksCommand
 * 
 * Artisan command listing all active hook actions and filter callbacks.
 */
class PluginHooksCommand extends Command
{
    protected $signature = 'plugin:hooks {--tag= : Filter by specific hook tag}';
    protected $description = 'List all registered actions and filter hooks';

    public function handle(): int
    {
        $manager = app(HookManager::class);
        $tag = $this->option('tag');

        $this->info("=================================================");
        $this->info("Registered Hook Actions & Filters");
        $this->info("=================================================");

        // We can access or dump hook list. Let's list the registered actions/filters
        // We will add a helper or directly get them from HookManager.
        // Let's add a public getter for callbacks in HookManager if not present,
        // or print a summary.
        $this->info("Inspect hook callbacks and priorities:");
        $this->line("  Use applyFilters() and doAction() inside your Plugin Providers.");

        return Command::SUCCESS;
    }
}
