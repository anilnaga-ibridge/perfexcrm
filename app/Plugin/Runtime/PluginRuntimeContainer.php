<?php

namespace App\Plugin\Runtime;

use Illuminate\Contracts\Container\Container;

/**
 * Class PluginRuntimeContainer
 * 
 * Scoped IoC service locator wrapper allowing plugins to bind services
 * and discover dynamic provider contracts.
 */
class PluginRuntimeContainer
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Bind a service interface to the container.
     */
    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $this->container->bind($abstract, $concrete, $shared);
    }

    /**
     * Bind a singleton service to the container.
     */
    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->container->singleton($abstract, $concrete);
    }

    /**
     * Resolve a service interface from the container.
     */
    public function resolve(string $abstract): mixed
    {
        return $this->container->make($abstract);
    }
}
