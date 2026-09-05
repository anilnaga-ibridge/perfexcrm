<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\DatabaseBackupCommand::class,
        Commands\ApiDataSeederCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:database')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }

    protected function load($paths)
    {
        try {
            parent::load($paths);
        } catch (\Throwable $e) {
            // Safely skip unreadable command directories in constrained environments
        }
    }
}
