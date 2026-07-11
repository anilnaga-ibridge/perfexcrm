<?php

namespace App\Plugin\Context;

use App\Contracts\Plugins\PluginInterface;
use App\Plugin\Storage\PluginStorageManager;

/**
 * Class PluginContext
 * 
 * Read-only runtime context scoped to an individual plugin.
 * Injected automatically into every PluginServiceProvider.
 */
class PluginContext
{
    /**
     * The underlying plugin instance.
     */
    protected PluginInterface $plugin;

    /**
     * PluginContext constructor.
     */
    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get the plugin's name.
     */
    public function getName(): string
    {
        return $this->plugin->getName();
    }

    /**
     * Get the plugin's slug/alias.
     */
    public function getAlias(): string
    {
        return $this->plugin->getAlias();
    }

    /**
     * Get the plugin's version.
     */
    public function getVersion(): string
    {
        return $this->plugin->getVersion();
    }

    /**
     * Get the plugin's filesystem directory path.
     */
    public function getPath(): string
    {
        return $this->plugin->getPath();
    }

    /**
     * Get the plugin's namespace.
     */
    public function getNamespace(): string
    {
        return $this->plugin->getNamespace();
    }

    /**
     * Get the plugin's current status.
     */
    public function getStatus(): string
    {
        return $this->plugin->isActive() ? 'active' : 'inactive';
    }

    /**
     * Get the plugin's dependencies list.
     */
    public function getDependencies(): array
    {
        return $this->plugin->getDependencies();
    }

    /**
     * Resolve the plugin's dedicated storage directory path.
     */
    public function getStoragePath(): string
    {
        return app(PluginStorageManager::class)->getStoragePath($this->getAlias());
    }

    /**
     * Resolve the plugin's dedicated cache directory path.
     */
    public function getCachePath(): string
    {
        return app(PluginStorageManager::class)->getCachePath($this->getAlias());
    }

    /**
     * Resolve the plugin's dedicated logs directory path.
     */
    public function getLogPath(): string
    {
        return app(PluginStorageManager::class)->getLogPath($this->getAlias());
    }

    /**
     * Resolve the plugin's dedicated temp directory path.
     */
    public function getTempPath(): string
    {
        return app(PluginStorageManager::class)->getTempPath($this->getAlias());
    }
}
