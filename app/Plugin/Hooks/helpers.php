<?php

use App\Plugin\Hooks\HookManager;

if (!function_exists('hook_manager')) {
    /**
     * Resolve the HookManager instance from the Laravel container.
     */
    function hook_manager(): HookManager
    {
        return app(HookManager::class);
    }
}

if (!function_exists('addAction')) {
    /**
     * Register an action listener globally.
     */
    function addAction(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        hook_manager()->addAction($tag, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('removeAction')) {
    /**
     * Remove an action listener globally.
     */
    function removeAction(string $tag, $callback, int $priority = 10): void
    {
        hook_manager()->removeAction($tag, $callback, $priority);
    }
}

if (!function_exists('doAction')) {
    /**
     * Fire all action listeners globally for the given tag.
     */
    function doAction(string $tag, ...$args): void
    {
        hook_manager()->doAction($tag, ...$args);
    }
}

if (!function_exists('addFilter')) {
    /**
     * Register a filter listener globally.
     */
    function addFilter(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        hook_manager()->addFilter($tag, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('removeFilter')) {
    /**
     * Remove a filter listener globally.
     */
    function removeFilter(string $tag, $callback, int $priority = 10): void
    {
        hook_manager()->removeFilter($tag, $callback, $priority);
    }
}

if (!function_exists('applyFilters')) {
    /**
     * Pass the initial value through all filter listeners globally for the given tag.
     */
    function applyFilters(string $tag, $value, ...$args)
    {
        return hook_manager()->applyFilters($tag, $value, ...$args);
    }
}
