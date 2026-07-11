<?php

namespace App\Plugin\Metadata;

use App\Plugin\Registries\PluginRegistry;
use App\Plugin\Registries\CapabilityRegistry;
use App\Plugin\Health\HealthMonitor;
use Illuminate\Support\Facades\Route;

/**
 * Class PluginMetadata
 * 
 * Formats full serializable metadata structures for plugin inspection.
 */
class PluginMetadata
{
    protected PluginRegistry $pluginRegistry;
    protected CapabilityRegistry $capabilityRegistry;
    protected HealthMonitor $healthMonitor;

    public function __construct(
        PluginRegistry $pluginRegistry,
        CapabilityRegistry $capabilityRegistry,
        HealthMonitor $healthMonitor
    ) {
        $this->pluginRegistry = $pluginRegistry;
        $this->capabilityRegistry = $capabilityRegistry;
        $this->healthMonitor = $healthMonitor;
    }

    /**
     * Compile and format all metadata fields for the given plugin.
     */
    public function get(string $alias): ?array
    {
        $plugin = $this->pluginRegistry->getPlugin($alias);
        if (!$plugin) {
            return null;
        }

        // Get capabilities and diagnostics
        $caps = $this->capabilityRegistry->getCapabilities($alias);
        $health = $this->healthMonitor->checkPlugin($alias);

        // Fetch routes matching plugin namespace
        $pluginRoutes = [];
        $ns = rtrim($plugin->getNamespace(), '\\');
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (str_starts_with($action, $ns)) {
                $pluginRoutes[] = [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'action' => $action,
                ];
            }
        }

        return [
            'name' => $plugin->getName(),
            'alias' => $plugin->getAlias(),
            'version' => $plugin->getVersion(),
            'namespace' => $plugin->getNamespace(),
            'path' => $plugin->getPath(),
            'status' => $plugin->isActive() ? 'active' : 'inactive',
            'dependencies' => $plugin->getDependencies(),
            'capabilities' => $caps,
            'health' => $health['status'],
            'health_checks' => $health['checks'],
            'routes' => $pluginRoutes,
        ];
    }
}
