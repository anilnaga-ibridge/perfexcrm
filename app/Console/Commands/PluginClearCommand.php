<?php

namespace App\Console\Commands;

use App\Services\PluginBridgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PluginClearCommand extends Command
{
    protected $signature = 'plugin:clear';
    protected $description = 'Clear all compiled plugin views, manifests, and descriptor caches';

    protected $bridgeService;

    public function __construct(PluginBridgeService $bridgeService)
    {
        parent::__construct();
        $this->bridgeService = $bridgeService;
    }

    public function handle()
    {
        $this->info('Clearing plugin caches...');

        // 1. Clear view compilation directory
        $viewsDir = storage_path('framework/views/plugins');
        if (File::isDirectory($viewsDir)) {
            File::cleanDirectory($viewsDir);
            $this->line('  - Cleared compiled legacy PHP views cache');
        }

        // 2. Clear discovery manifest caches
        $modulesDir = base_path('Modules');
        if (File::isDirectory($modulesDir)) {
            $folders = File::directories($modulesDir);
            foreach ($folders as $folder) {
                $alias = basename($folder);
                $this->bridgeService->clearCache($alias);
            }
            $this->line('  - Cleared plugin discovery manifests');
        }

        $this->info('Plugin caches cleared successfully!');
        return 0;
    }
}
