<?php

namespace App\Plugin;

use App\Contracts\Plugins\PluginInterface;

/**
 * Class PluginInstance
 * 
 * Standard implementation of the PluginInterface representing a loaded plugin.
 */
class PluginInstance implements PluginInterface
{
    protected string $name;
    protected string $alias;
    protected string $version;
    protected string $namespace;
    protected string $path;
    protected string $status;
    protected array $dependencies;

    public function __construct(array $attributes)
    {
        $this->name = $attributes['name'] ?? '';
        $this->alias = $attributes['alias'] ?? '';
        $this->version = $attributes['version'] ?? '1.0.0';
        $this->namespace = $attributes['namespace'] ?? '';
        $this->path = $attributes['path'] ?? '';
        $this->status = $attributes['status'] ?? 'installed';
        $this->dependencies = $attributes['depends'] ?? [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Get the raw status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
