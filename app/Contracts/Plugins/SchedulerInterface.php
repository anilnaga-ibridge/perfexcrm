<?php

namespace App\Contracts\Plugins;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Interface SchedulerInterface
 * 
 * Defines how plugins hook background tasks into the global Laravel Console Scheduler.
 */
interface SchedulerInterface
{
    /**
     * Define the task execution schedule (cron pattern, interval, etc.).
     */
    public function schedule(Schedule $schedule): void;
}
