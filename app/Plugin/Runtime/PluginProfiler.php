<?php

namespace App\Plugin\Runtime;

/**
 * Class PluginProfiler
 * 
 * Captures granular runtime diagnostics, profiling memory allocation
 * and timings of providers, hooks, and views.
 */
class PluginProfiler
{
    protected array $profiles = [];

    /**
     * Start profiling a specific component action.
     */
    public function start(string $pluginAlias, string $category, string $item): void
    {
        $this->profiles[] = [
            'plugin' => $pluginAlias,
            'category' => $category,
            'item' => $item,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
        ];
    }

    /**
     * Stop profiling a specific component action.
     */
    public function stop(string $pluginAlias, string $category, string $item): void
    {
        foreach ($this->profiles as &$profile) {
            if ($profile['plugin'] === $pluginAlias &&
                $profile['category'] === $category &&
                $profile['item'] === $item &&
                !isset($profile['duration'])) {
                
                $profile['end_time'] = microtime(true);
                $profile['end_memory'] = memory_get_usage();
                $profile['duration'] = $profile['end_time'] - $profile['start_time'];
                $profile['memory_used'] = $profile['end_memory'] - $profile['start_memory'];
                break;
            }
        }
    }

    /**
     * Get compiled execution profile logs.
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }
}
