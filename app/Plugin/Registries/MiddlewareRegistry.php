<?php

namespace App\Plugin\Registries;

use Illuminate\Routing\Router;

/**
 * Class MiddlewareRegistry
 * 
 * Evolved registry storing and binding plugin route middleware to Laravel router.
 */
class MiddlewareRegistry
{
    /**
     * The Laravel router instance.
     */
    protected Router $router;

    /**
     * Map of alias name to middleware class.
     */
    protected array $middlewares = [];

    /**
     * MiddlewareRegistry constructor.
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register a route middleware alias.
     */
    public function register(string $name, string $middlewareClass): void
    {
        $this->middlewares[$name] = $middlewareClass;
        $this->router->aliasMiddleware($name, $middlewareClass);
    }

    /**
     * Push a middleware class to a global middleware group.
     */
    public function pushToGroup(string $group, string $middlewareClass): void
    {
        $this->router->pushMiddlewareToGroup($group, $middlewareClass);
    }

    /**
     * Get all registered middleware aliases.
     */
    public function all(): array
    {
        return $this->middlewares;
    }
}
