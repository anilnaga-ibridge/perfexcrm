<?php

namespace App\Contracts\Plugins;

/**
 * Interface PluginInterface
 * 
 * Defines the core metadata and lifecycle queries for iBridge plugins.
 */
interface PluginInterface
{
    /**
     * Get the plugin's unique display name.
     */
    public function getName(): string;

    /**
     * Get the plugin's unique slug/alias.
     */
    public function getAlias(): string;

    /**
     * Get the plugin's current semantic version.
     */
    public function getVersion(): string;

    /**
     * Get the plugin's root namespace prefix (e.g. "Modules\HRM\\").
     */
    public function getNamespace(): string;

    /**
     * Get the plugin's absolute directory path.
     */
    public function getPath(): string;

    /**
     * Determine if the plugin is currently active.
     */
    public function isActive(): bool;

    /**
     * Get the dependency aliases required by this plugin.
     */
    public function getDependencies(): array;
}
