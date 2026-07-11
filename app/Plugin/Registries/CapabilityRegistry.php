<?php

namespace App\Plugin\Registries;

use App\Plugin\Registries\PluginRegistry;
use Illuminate\Support\Facades\File;

/**
 * Class CapabilityRegistry
 * 
 * Auto-discovers and caches structural capabilities (routes, widgets, commands, menus)
 * owned by each active plugin.
 */
class CapabilityRegistry
{
    protected PluginRegistry $pluginRegistry;

    public function __construct(PluginRegistry $pluginRegistry)
    {
        $this->pluginRegistry = $pluginRegistry;
    }

    /**
     * Inspect and return capabilities of a specific plugin.
     */
    public function getCapabilities(string $pluginAlias): array
    {
        $plugin = $this->pluginRegistry->getPlugin($pluginAlias);
        if (!$plugin) {
            return [];
        }

        $path = $plugin->getPath();
        $capabilities = [
            'routes' => [
                'web' => File::exists($path . '/routes/web.php') || File::exists($path . '/Routes/web.php'),
                'api' => File::exists($path . '/routes/api.php') || File::exists($path . '/Routes/api.php'),
                'console' => File::exists($path . '/routes/console.php') || File::exists($path . '/Routes/console.php'),
            ],
            'widgets' => File::isDirectory($path . '/Widgets'),
            'commands' => File::isDirectory($path . '/Console/Commands'),
            'policies' => File::isDirectory($path . '/Policies'),
            'jobs' => File::isDirectory($path . '/Jobs'),
            'views' => File::isDirectory($path . '/Views'),
            'assets' => File::isDirectory($path . '/Assets'),
            'translations' => File::isDirectory($path . '/language'),
            'migrations' => File::isDirectory($path . '/Database/Migrations'),
        ];

        return $capabilities;
    }

    /**
     * Get consolidated capabilities across all active plugins.
     */
    public function all(): array
    {
        $report = [];
        foreach ($this->pluginRegistry->getActivePlugins() as $plugin) {
            $report[$plugin->getAlias()] = $this->getCapabilities($plugin->getAlias());
        }
        return $report;
    }
}
