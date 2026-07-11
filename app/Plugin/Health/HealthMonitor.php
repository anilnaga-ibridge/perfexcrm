<?php

namespace App\Plugin\Health;

use App\Plugin\Registries\PluginRegistry;
use App\Plugin\Versioning\VersionManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

/**
 * Class HealthMonitor
 * 
 * Runs diagnostic checks on active plugins and outputs a structured health report.
 */
class HealthMonitor
{
    protected PluginRegistry $registry;
    protected VersionManager $versionManager;

    public function __construct(PluginRegistry $registry, VersionManager $versionManager)
    {
        $this->registry = $registry;
        $this->versionManager = $versionManager;
    }

    /**
     * Run full diagnostics for a specific plugin.
     */
    public function checkPlugin(string $alias): array
    {
        $plugin = $this->registry->getPlugin($alias);
        if (!$plugin) {
            return [
                'status' => 'error',
                'message' => "Plugin '{$alias}' not found in registry.",
                'checks' => [],
            ];
        }

        $checks = [
            'database' => $this->checkDatabase(),
            'dependencies' => $this->checkDependencies($plugin),
            'routes' => $this->checkRoutes($plugin),
            'assets' => $this->checkAssets($plugin),
            'storage' => $this->checkStorage($plugin),
            'cache' => $this->checkCache(),
            'migrations' => $this->checkMigrations($plugin),
        ];

        // Overall status is 'healthy' if no checks have an error status
        $status = 'healthy';
        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                $status = 'unhealthy';
                break;
            }
        }

        return [
            'alias' => $plugin->getAlias(),
            'name' => $plugin->getName(),
            'status' => $status,
            'checks' => $checks,
        ];
    }

    /**
     * Check if database connection is alive.
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connection is healthy.'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Check if dependencies are resolved.
     */
    protected function checkDependencies($plugin): array
    {
        try {
            $installed = $this->registry->getPlugins();
            $this->versionManager->validateDependencies($plugin, $installed);
            return ['status' => 'ok', 'message' => 'All dependencies are satisfied.'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check routes registered for this plugin.
     */
    protected function checkRoutes($plugin): array
    {
        $ns = rtrim($plugin->getNamespace(), '\\');
        $routes = Route::getRoutes()->getRoutes();
        $count = 0;

        foreach ($routes as $route) {
            $action = $route->getActionName();
            if (str_starts_with($action, $ns)) {
                $count++;
            }
        }

        return [
            'status' => 'ok',
            'message' => "Found {$count} registered route(s) under namespace '{$ns}'.",
            'count' => $count,
        ];
    }

    /**
     * Check published assets.
     */
    protected function checkAssets($plugin): array
    {
        $publicDir = public_path('modules/' . strtolower($plugin->getAlias()));
        if (File::exists($plugin->getPath() . '/Assets')) {
            if (!File::isDirectory($publicDir)) {
                return ['status' => 'error', 'message' => 'Assets exist in source but have not been published to public folder.'];
            }
            return ['status' => 'ok', 'message' => 'Assets are published and available.'];
        }

        return ['status' => 'ok', 'message' => 'No static assets defined for this plugin.'];
    }

    /**
     * Check plugin storage permissions.
     */
    protected function checkStorage($plugin): array
    {
        $path = $plugin->getPath();
        if (!is_writable($path)) {
            return ['status' => 'warning', 'message' => "Plugin root directory '{$path}' is not writable. Automatic updates might fail."];
        }
        return ['status' => 'ok', 'message' => 'Plugin directory permissions are healthy.'];
    }

    /**
     * Check cache health.
     */
    protected function checkCache(): array
    {
        try {
            Cache::put('health_check_temp', true, 10);
            Cache::forget('health_check_temp');
            return ['status' => 'ok', 'message' => 'Cache driver is responsive.'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Cache driver error: ' . $e->getMessage()];
        }
    }

    /**
     * Verify migrations matching the plugin are loaded.
     */
    protected function checkMigrations($plugin): array
    {
        $migrationsPath = $plugin->getPath() . '/Database/Migrations';
        if (!File::isDirectory($migrationsPath)) {
            return ['status' => 'ok', 'message' => 'No migrations specified.'];
        }

        $files = File::files($migrationsPath);
        if (empty($files)) {
            return ['status' => 'ok', 'message' => 'No migration files found.'];
        }

        // Just checking if migrated files count matches database table runs
        try {
            if (Schema::hasTable('migrations')) {
                $dbMigrations = DB::table('migrations')->pluck('migration')->toArray();
                $missing = [];

                foreach ($files as $file) {
                    $migName = $file->getBasename('.php');
                    // Match simple substring in migration table
                    $found = false;
                    foreach ($dbMigrations as $dbMig) {
                        if (str_contains($dbMig, $migName)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $missing[] = $migName;
                    }
                }

                if (!empty($missing)) {
                    return [
                        'status' => 'error',
                        'message' => 'Found ' . count($missing) . ' pending migration(s): ' . implode(', ', $missing),
                    ];
                }
            }
            return ['status' => 'ok', 'message' => 'All migration files have been successfully run.'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Failed to read migrations table: ' . $e->getMessage()];
        }
    }
}
