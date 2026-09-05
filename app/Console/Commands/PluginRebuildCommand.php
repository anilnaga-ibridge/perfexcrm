<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PluginRebuildCommand extends Command
{
    protected $signature = 'plugin:rebuild';
    protected $description = 'Clear and re-warm all discovery manifests and compiled plugin views';

    public function handle()
    {
        $this->info('Starting plugin rebuild...');

        $this->line('Running plugin:clear...');
        Artisan::call('plugin:clear');
        $this->line(Artisan::output());

        $this->line('Running plugin:cache...');
        Artisan::call('plugin:cache');
        $this->line(Artisan::output());

        $this->info('Plugin rebuild completed successfully!');
        return 0;
    }
}
