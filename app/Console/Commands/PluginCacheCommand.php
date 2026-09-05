<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Services\LegacyViewRenderer;
use App\Services\PluginBridgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PluginCacheCommand extends Command
{
    protected $signature = 'plugin:cache';
    protected $description = 'Prebuild discovery manifest caches and pre-compile legacy PHP views for all modules';

    protected $bridgeService;
    protected $viewRenderer;
    protected $kernel;

    public function __construct(
        PluginBridgeService $bridgeService,
        LegacyViewRenderer $viewRenderer,
        \App\Plugin\Kernel\RuntimeKernel $kernel
    ) {
        parent::__construct();
        $this->bridgeService = $bridgeService;
        $this->viewRenderer = $viewRenderer;
        $this->kernel = $kernel;
    }

    public function handle()
    {
        $this->info('Starting plugin cache warm-up...');

        $modulesDir = base_path('Modules');
        if (!File::isDirectory($modulesDir)) {
            $this->warn('Modules directory does not exist.');
            return 0;
        }

        $activeModules = Module::where('status', 'active')->get();
        if ($activeModules->isEmpty()) {
            $this->info('No active modules found to cache.');
            return 0;
        }

        foreach ($activeModules as $module) {
            $alias = $module->alias;
            $this->info("Warming up cache for module: [{$alias}]");

            // 1. Clear existing cache to guarantee fresh warm-up
            $this->bridgeService->clearCache($alias);

            // 2. Build and cache the manifest
            $startTime = microtime(true);
            $manifest = $this->bridgeService->getManifest($alias);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if (empty($manifest)) {
                $this->error("Failed to generate manifest for [{$alias}]. Skipping.");
                continue;
            }

            $this->line("  - Manifest cached successfully ({$duration}ms)");

            // 3. Pre-compile legacy views
            $viewsDir = $manifest['views_dir'];
            if (File::isDirectory($viewsDir)) {
                $viewFiles = File::allFiles($viewsDir);
                $compiledCount = 0;

                foreach ($viewFiles as $file) {
                    if ($file->getExtension() === 'php') {
                        // Trigger render with empty data to force compilation
                        $this->viewRenderer->render($file->getRealPath(), []);
                        $compiledCount++;
                    }
                }

                $this->line("  - Compiled {$compiledCount} legacy PHP views");
            }
        }

        $this->line("  - Warming up typed descriptors in Kernel...");
        $this->kernel->bootstrap();

        $this->info('Plugin cache warm-up completed successfully!');
        return 0;
    }
}
