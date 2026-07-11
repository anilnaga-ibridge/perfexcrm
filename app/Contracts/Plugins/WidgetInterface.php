<?php

namespace App\Contracts\Plugins;

/**
 * Interface WidgetInterface
 * 
 * Defines the structure for dynamic dashboard widgets registered by plugins.
 */
interface WidgetInterface
{
    /**
     * Get the widget's unique identifier.
     */
    public function getId(): string;

    /**
     * Get the widget's human-readable title.
     */
    public function getTitle(): string;

    /**
     * Get the size/width of the widget (e.g. "full", "half", "third").
     */
    public function getSize(): string;

    /**
     * Render the widget's HTML or Vue component path.
     */
    public function render(): string;

    /**
     * Get any data required for the widget to render.
     */
    public function getData(): array;
}
