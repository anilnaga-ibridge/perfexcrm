<?php

namespace App\Contracts\Plugins;

/**
 * Interface HookInterface
 * 
 * Defines the contract for registerable actions/filters in the global hook engine.
 */
interface HookInterface
{
    /**
     * Get the hook name/tag (e.g. "dashboard.render").
     */
    public function getTag(): string;

    /**
     * Get the priority of execution (default: 10).
     */
    public function getPriority(): int;

    /**
     * Execute the hook listener callback.
     */
    public function execute(...$args);
}
