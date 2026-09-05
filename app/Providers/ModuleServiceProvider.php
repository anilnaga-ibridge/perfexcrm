<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Convert a module alias (or name) to a valid PascalCase PHP namespace segment.
     * e.g. "my-awesome-module" → "MyAwesomeModule"
     */
    private function aliasToNamespace(string $value): string
    {
        $namespace = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
        // PHP namespaces cannot start with a digit
        if (preg_match('/^[0-9]/', $namespace)) {
            $namespace = 'Mod_' . $namespace;
        }
        return $namespace;
    }

    /**
     * Resolve module directory path with backward-compatible fallback.
     */
    private function resolveModulePath(string $alias, string $name): string
    {
        $path = base_path("Modules/{$alias}");
        if (is_dir($path)) {
            return $path;
        }
        // Legacy fallback: modules installed with display-name-based directories
        $legacyPath = base_path("Modules/{$name}");
        if (is_dir($legacyPath)) {
            return $legacyPath;
        }
        return $path;
    }

    /**
     * Resolve the correct PSR-4 / PHP namespace prefix for a module.
     * Uses alias-based namespace for new modules, name-based for legacy.
     */
    private function resolveNamespace(string $alias, string $name): string
    {
        // If alias-based directory exists, this is a new-style module
        if (is_dir(base_path("Modules/{$alias}"))) {
            return $this->aliasToNamespace($alias);
        }
        // Legacy fallback: use name (which matches the legacy directory name)
        return $this->aliasToNamespace($name);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1. Bind Evolved registries and managers in IoC container
        $this->app->singleton(\App\Plugin\Hooks\HookManager::class, function ($app) {
            return new \App\Plugin\Hooks\HookManager();
        });

        $this->app->singleton(\App\Plugin\Events\EventBus::class, function ($app) {
            return new \App\Plugin\Events\EventBus($app['events']);
        });

        $this->app->singleton(\App\Plugin\Registries\PluginRegistry::class, function ($app) {
            return new \App\Plugin\Registries\PluginRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\WidgetRegistry::class, function ($app) {
            return new \App\Plugin\Registries\WidgetRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\CommandRegistry::class, function ($app) {
            return new \App\Plugin\Registries\CommandRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\SchedulerRegistry::class, function ($app) {
            return new \App\Plugin\Registries\SchedulerRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\SearchRegistry::class, function ($app) {
            return new \App\Plugin\Registries\SearchRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\ExportRegistry::class, function ($app) {
            return new \App\Plugin\Registries\ExportRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\ImportRegistry::class, function ($app) {
            return new \App\Plugin\Registries\ImportRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\NotificationRegistry::class, function ($app) {
            return new \App\Plugin\Registries\NotificationRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\PolicyRegistry::class, function ($app) {
            return new \App\Plugin\Registries\PolicyRegistry();
        });

        $this->app->singleton(\App\Plugin\Registries\MiddlewareRegistry::class, function ($app) {
            return new \App\Plugin\Registries\MiddlewareRegistry($app['router']);
        });

        $this->app->singleton(\App\Plugin\Registries\ConfigRegistry::class, function ($app) {
            return new \App\Plugin\Registries\ConfigRegistry($app['config']);
        });

        $this->app->singleton(\App\Plugin\Assets\AssetPipeline::class, function ($app) {
            return new \App\Plugin\Assets\AssetPipeline();
        });

        $this->app->singleton(\App\Plugin\Versioning\VersionManager::class, function ($app) {
            return new \App\Plugin\Versioning\VersionManager();
        });

        $this->app->singleton(\App\Plugin\Health\HealthMonitor::class, function ($app) {
            return new \App\Plugin\Health\HealthMonitor(
                $app->make(\App\Plugin\Registries\PluginRegistry::class),
                $app->make(\App\Plugin\Versioning\VersionManager::class)
            );
        });

        $this->app->singleton(\App\Plugin\Marketplace\MarketplaceManager::class, function ($app) {
            return new \App\Plugin\Marketplace\MarketplaceManager();
        });

        // 2. Load global WordPress-style actions/filters helper functions
        $helpersPath = app_path('Plugin/Hooks/helpers.php');
        if (@is_readable($helpersPath)) {
            require_once $helpersPath;
        }

        // 3. Register PSR-4 autoloader namespaces for active modules
        $activeModules = $this->getActiveModules();

        if (!empty($activeModules)) {
            $loader = require base_path('vendor/autoload.php');

            foreach ($activeModules as $module) {
                $moduleAlias = $module->alias;
                $moduleName = $module->name;
                $modulePath = $this->resolveModulePath($moduleAlias, $moduleName);
                $nsPrefix = $this->resolveNamespace($moduleAlias, $moduleName);

                if (is_dir($modulePath)) {
                    $loader->addPsr4("Modules\\{$nsPrefix}\\", $modulePath);
                }
            }
        }

        // 4. Register newly discovered active Plugin Service Providers
        $registry = $this->app->make(\App\Plugin\Registries\PluginRegistry::class);
        foreach ($registry->getActivePlugins() as $plugin) {
            $providerClass = $registry->getProviderFor($plugin->getAlias());
            if ($providerClass && class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }

        // 5. Register SDK CLI Artisan Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\PluginDoctorCommand::class,
                \App\Console\Commands\PluginPublishCommand::class,
                \App\Console\Commands\PluginHealthCommand::class,
                \App\Console\Commands\PluginCacheCommand::class,
                \App\Console\Commands\PluginClearCommand::class,
                \App\Console\Commands\PluginHooksCommand::class,
                \App\Console\Commands\PluginEventsCommand::class,
                \App\Console\Commands\PluginDependenciesCommand::class,
                \App\Console\Commands\PluginInfoCommand::class,
                \App\Console\Commands\PluginStorageCommand::class,
                \App\Console\Commands\PluginUpgradeCommand::class,
                \App\Console\Commands\PluginRollbackCommand::class,
                \App\Console\Commands\PluginPackageCommand::class,
                \App\Console\Commands\PluginSignCommand::class,
                \App\Console\Commands\PluginVerifyCommand::class,
                \App\Console\Commands\PluginReleaseCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            // 1. Bootstrap dynamically registered commands
            if (class_exists(\App\Plugin\Registries\CommandRegistry::class)) {
                $this->app->make(\App\Plugin\Registries\CommandRegistry::class)->bootstrap();
            }

            // 2. Bootstrap dynamic scheduler bindings when Scheduler resolves
            if ($this->app->runningInConsole() && class_exists(\App\Plugin\Registries\SchedulerRegistry::class)) {
                $this->app->resolving(\Illuminate\Console\Scheduling\Schedule::class, function ($schedule) {
                    $this->app->make(\App\Plugin\Registries\SchedulerRegistry::class)->bootstrap($schedule);
                });
            }

            // 3. Backward-compatible dynamic loading for legacy modules (without providers)
            if (!class_exists(\App\Plugin\Registries\PluginRegistry::class)) {
                return;
            }

            $registry = $this->app->make(\App\Plugin\Registries\PluginRegistry::class);
            $activeModules = $this->getActiveModules();

        if (!empty($activeModules)) {
            foreach ($activeModules as $module) {
                $moduleAlias = $module->alias;
                $moduleName = $module->name;

                // Ensure legacy CodeIgniter-style symlinks exist
                try {
                    \App\Services\ModuleManager::createLegacySymlink($moduleAlias);
                } catch (\Exception $e) {}

                $nsPrefix = $this->resolveNamespace($moduleAlias, $moduleName);
                $alias = strtolower($moduleAlias);
                $modulePath = $this->resolveModulePath($moduleAlias, $moduleName);

                if (is_dir($modulePath)) {
                    // Check if module has a Service Provider registered
                    $hasProvider = $registry->getProviderFor($moduleAlias) !== null;
                    
                    // Discover and register security policies
                    $this->app->make(\App\Plugin\Registries\PolicyRegistry::class)->discover($modulePath, "Modules\\{$nsPrefix}\\" );

                    if ($hasProvider) {
                        // Let the provider handle routes, views, translations, and migrations boot!
                        continue;
                    }

                    // --- FALLBACK LEGACY BOOTSTRAPPER ---
                    // 1a. Dynamic Web Routes
                    $webRoutesPath = "{$modulePath}/routes/web.php";
                    if (!file_exists($webRoutesPath)) {
                        $webRoutesPath = "{$modulePath}/Routes/web.php";
                    }
                    if (file_exists($webRoutesPath)) {
                        Route::middleware('web')
                            ->namespace("Modules\\{$nsPrefix}\\Controllers")
                            ->group($webRoutesPath);
                    }

                    // 1b. Dynamic API Routes
                    $apiRoutesPath = "{$modulePath}/routes/api.php";
                    if (!file_exists($apiRoutesPath)) {
                        $apiRoutesPath = "{$modulePath}/Routes/api.php";
                    }
                    if (file_exists($apiRoutesPath)) {
                        Route::middleware(['api', 'auth:sanctum'])
                            ->prefix('api')
                            ->namespace("Modules\\{$nsPrefix}\\Controllers\Api")
                            ->group($apiRoutesPath);
                    }
                    $viewsPath = "{$modulePath}/Views";
                    if (is_dir($viewsPath)) {
                        $this->loadViewsFrom($viewsPath, $alias);
                    }

                    // 3. Dynamic Migrations
                    $migrationsPath = "{$modulePath}/Database/Migrations";
                    if (is_dir($migrationsPath)) {
                        $this->loadMigrationsFrom($migrationsPath);
                    }

                    // 4. Universal CodeIgniter / iBridge Hook & Helper Loader
                    $entryCandidates = [
                        "{$modulePath}/{$moduleAlias}.php",
                        "{$modulePath}/" . str_replace('-', '_', $moduleAlias) . ".php",
                    ];
                    foreach ($entryCandidates as $entryFile) {
                        if (file_exists($entryFile)) {
                            try {
                                require_once base_path('app/Services/CICompatLayer.php');
                                require_once $entryFile;
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::warning("[Module Hook Boot Error] Module '{$moduleAlias}': " . $e->getMessage());
                            }
                            break;
                        }
                    }
                }
            }
        }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ModuleServiceProvider boot warning: " . $e->getMessage());
        }
    }

    /**
     * Fetch active modules list from the database, catching any connection/existence errors.
     */
    private function getActiveModules(): array
    {
        try {
            // DB exists check to prevent failures during early composer commands or migrations
            if (class_exists(\Illuminate\Support\Facades\Schema::class) && Schema::hasTable('modules')) {
                return DB::table('modules')
                    ->where('status', 'active')
                    ->get(['name', 'alias'])
                    ->toArray();
            }
        } catch (\Exception $e) {
            // Database is not yet accessible (e.g. migration running)
        }

        return [];
    }
}
