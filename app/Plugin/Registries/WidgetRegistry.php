<?php

namespace App\Plugin\Registries;

use App\Contracts\Plugins\WidgetInterface;

/**
 * Class WidgetRegistry
 * 
 * Central registry for dashboard widgets supplied by plugins.
 */
class WidgetRegistry
{
    /**
     * Registered widget items.
     * 
     * @var WidgetInterface[]
     */
    protected array $widgets = [];

    /**
     * Register a new dashboard widget.
     */
    public function register(WidgetInterface $widget): void
    {
        $this->widgets[$widget->getId()] = $widget;
    }

    /**
     * Get all registered widgets.
     * 
     * @return WidgetInterface[]
     */
    public function all(): array
    {
        return array_values($this->widgets);
    }

    /**
     * Find a specific widget by ID.
     */
    public function find(string $id): ?WidgetInterface
    {
        return $this->widgets[$id] ?? null;
    }
}
