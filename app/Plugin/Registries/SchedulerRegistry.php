<?php

namespace App\Plugin\Registries;

use App\Contracts\Plugins\SchedulerInterface;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Class SchedulerRegistry
 * 
 * Evolved task schedule registry merging plugin tasks into the core scheduler.
 */
class SchedulerRegistry
{
    /**
     * Registered scheduler instances.
     * 
     * @var SchedulerInterface[]
     */
    protected array $schedulers = [];

    /**
     * Register a plugin scheduler hook.
     */
    public function register(SchedulerInterface $scheduler): void
    {
        $this->schedulers[] = $scheduler;
    }

    /**
     * Bind all registered schedules into Laravel's active Console Schedule.
     */
    public function bootstrap(Schedule $schedule): void
    {
        foreach ($this->schedulers as $scheduler) {
            $scheduler->schedule($schedule);
        }
    }

    /**
     * Get all registered schedulers.
     */
    public function all(): array
    {
        return $this->schedulers;
    }
}
