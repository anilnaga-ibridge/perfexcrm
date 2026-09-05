<?php

namespace App\Plugin\Kernel;

class ServiceRegistry
{
    protected array $services = [];

    /**
     * Register a plugin service instance.
     */
    public function register(string $contract, object $instance): void
    {
        $this->services[$contract] = $instance;
    }

    /**
     * Resolve a registered plugin service.
     */
    public function resolve(string $contract): ?object
    {
        return $this->services[$contract] ?? null;
    }

    /**
     * Get all registered services.
     */
    public function all(): array
    {
        return $this->services;
    }
}
