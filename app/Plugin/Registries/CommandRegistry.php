<?php

namespace App\Plugin\Registries;

use Illuminate\Console\Application as Artisan;

/**
 * Class CommandRegistry
 * 
 * Dynamic registry that stores and binds plugin-provided console commands to Artisan.
 */
class CommandRegistry
{
    /**
     * Array of command class names.
     */
    protected array $commands = [];

    /**
     * Register an Artisan command class name.
     */
    public function register(string $commandClass): void
    {
        if (!in_array($commandClass, $this->commands)) {
            $this->commands[] = $commandClass;
        }
    }

    /**
     * Register all cached commands with the Artisan application.
     */
    public function bootstrap(): void
    {
        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands($this->commands);
        });
    }

    /**
     * Get all registered command classes.
     */
    public function all(): array
    {
        return $this->commands;
    }
}
