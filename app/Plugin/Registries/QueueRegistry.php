<?php

namespace App\Plugin\Registries;

/**
 * Class QueueRegistry
 * 
 * Evolved queue registry storing custom queued jobs and configurations defined by plugins.
 */
class QueueRegistry
{
    /**
     * Map of queue name to job/worker settings.
     */
    protected array $queues = [];

    /**
     * Register a new queue or connection.
     */
    public function register(string $queueName, array $config = []): void
    {
        $this->queues[$queueName] = $config;
    }

    /**
     * Get all registered queues.
     */
    public function all(): array
    {
        return $this->queues;
    }
}
