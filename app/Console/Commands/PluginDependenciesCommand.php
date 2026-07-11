<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugin\Dependency\DependencyGraph;
use App\Plugin\Registries\PluginRegistry;

/**
 * Class PluginDependenciesCommand
 * 
 * Artisan command displaying topological sorting and dependency trees.
 */
class PluginDependenciesCommand extends Command
{
    protected $signature = 'plugin:dependencies';
    protected $description = 'Analyze and display the full plugin dependency tree';

    public function handle(): int
    {
        $registry = app(PluginRegistry::class);
        $graph = app(DependencyGraph::class);

        $plugins = $registry->getPlugins();
        $graph->build($plugins);

        $this->info("=================================================");
        $this->info("Plugin Dependency Analysis & Tree");
        $this->info("=================================================");

        try {
            $order = $graph->getActivationOrder();
            $this->comment("Safe Boot / Activation Order:");
            foreach ($order as $index => $alias) {
                $this->line("  " . ($index + 1) . ". {$alias}");
            }

            $this->newLine();
            $this->comment("Dependency Tree Visualization:");
            $this->line($graph->generateTree());
        } catch (\Throwable $e) {
            $this->error("Dependency Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
