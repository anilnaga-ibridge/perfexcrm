<?php

namespace App\Plugin\Providers;

use Illuminate\Support\ServiceProvider;
use App\Plugin\Registries\WidgetRegistry;
use App\Plugin\Registries\CommandRegistry;
use App\Plugin\Registries\SchedulerRegistry;
use App\Plugin\Registries\SearchRegistry;
use App\Plugin\Registries\ExportRegistry;
use App\Plugin\Registries\ImportRegistry;
use App\Plugin\Registries\NotificationRegistry;
use App\Plugin\Registries\PolicyRegistry;
use App\Plugin\Registries\MiddlewareRegistry;
use App\Plugin\Registries\ConfigRegistry;
use App\Plugin\Events\EventBus;
use App\Plugin\Hooks\HookManager;
use App\Plugin\Assets\AssetPipeline;
use App\Contracts\Plugins\WidgetInterface;
use App\Contracts\Plugins\SearchInterface;
use App\Contracts\Plugins\SchedulerInterface;
use App\Contracts\Plugins\NotificationInterface;
use App\Contracts\Plugins\AssetInterface;
use Illuminate\Support\Facades\Route;

/**
 * Class PluginServiceProvider
 * 
 * Base Service Provider for all iBridge plugins.
 * Provides helper registration functions utilizing the centralized IoC Registries.
 */
abstract class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        // Override in plugins
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        // Override in plugins
    }
    /**
     * Register dynamic Web/API routes.
     */
    protected function registerRoutes(string $webPath = null, string $apiPath = null, string $namespace = null): void
    {
        if ($webPath && file_exists($webPath)) {
            $route = Route::middleware('web');
            if ($namespace) {
                $route->namespace($namespace);
            }
            $route->group($webPath);
        }

        if ($apiPath && file_exists($apiPath)) {
            $route = Route::middleware('api')->prefix('api');
            if ($namespace) {
                $route->namespace($namespace);
            }
            $route->group($apiPath);
        }
    }

    /**
     * Register dynamic Blade views.
     */
    protected function registerViews(string $path, string $namespace): void
    {
        if (is_dir($path)) {
            $this->loadViewsFrom($path, $namespace);
        }
    }

    /**
     * Register dynamic translations.
     */
    protected function registerTranslations(string $path, string $namespace): void
    {
        if (is_dir($path)) {
            $this->loadTranslationsFrom($path, $namespace);
        }
    }

    /**
     * Register dynamic Artisan commands.
     */
    protected function registerCommands(array $commandClasses): void
    {
        $registry = $this->app->make(CommandRegistry::class);
        foreach ($commandClasses as $class) {
            $registry->register($class);
        }
    }

    /**
     * Register custom dashboard widgets.
     */
    protected function registerWidgets(array $widgets): void
    {
        $registry = $this->app->make(WidgetRegistry::class);
        foreach ($widgets as $widget) {
            if ($widget instanceof WidgetInterface) {
                $registry->register($widget);
            }
        }
    }

    /**
     * Register search providers.
     */
    protected function registerSearch(SearchInterface $provider): void
    {
        $this->app->make(SearchRegistry::class)->register($provider);
    }

    /**
     * Register scheduler tasks.
     */
    protected function registerScheduler(SchedulerInterface $scheduler): void
    {
        $this->app->make(SchedulerRegistry::class)->register($scheduler);
    }

    /**
     * Register notification channels.
     */
    protected function registerNotifications(NotificationInterface $channel): void
    {
        $this->app->make(NotificationRegistry::class)->register($channel);
    }

    /**
     * Register security policies.
     */
    protected function registerPolicies(array $policies): void
    {
        $registry = $this->app->make(PolicyRegistry::class);
        foreach ($policies as $modelClass => $policyClass) {
            $registry->register($modelClass, $policyClass);
        }
    }

    /**
     * Register custom route middleware.
     */
    protected function registerMiddleware(string $name, string $middlewareClass): void
    {
        $this->app->make(MiddlewareRegistry::class)->register($name, $middlewareClass);
    }

    /**
     * Register custom configuration options.
     */
    protected function registerConfig(string $key, array $values): void
    {
        $this->app->make(ConfigRegistry::class)->merge($key, $values);
    }

    /**
     * Register custom hook listeners.
     */
    protected function registerAction(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->app->make(HookManager::class)->addAction($tag, $callback, $priority, $acceptedArgs);
    }

    /**
     * Register custom filter listeners.
     */
    protected function registerFilter(string $tag, $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->app->make(HookManager::class)->addFilter($tag, $callback, $priority, $acceptedArgs);
    }

    /**
     * Register custom event listeners in the EventBus.
     */
    protected function registerEventListener(string|array $events, mixed $listener): void
    {
        $this->app->make(EventBus::class)->listen($events, $listener);
    }

    /**
     * Register plugin public asset directories.
     */
    protected function registerAssets(AssetInterface $asset): void
    {
        $this->app->make(AssetPipeline::class)->register($asset);
    }
}
