<?php

namespace App\Plugin\Runtime;

/**
 * Class PluginMetrics
 * 
 * Aggregates runtime telemetry metrics including execution time, query count,
 * cache hit ratio, and event counts.
 */
class PluginMetrics
{
    protected array $metrics = [
        'boot_times' => [],
        'queries' => [],
        'cache_hits' => 0,
        'cache_misses' => 0,
        'hook_triggers' => 0,
        'event_triggers' => 0,
    ];

    /**
     * Increment metric count.
     */
    public function increment(string $key, int $value = 1): void
    {
        if (isset($this->metrics[$key])) {
            $this->metrics[$key] += $value;
        }
    }

    /**
     * Record execution or boot time for a plugin.
     */
    public function recordTime(string $pluginAlias, string $stage, float $duration): void
    {
        $this->metrics['boot_times'][$pluginAlias][$stage] = $duration;
    }

    /**
     * Record query counts.
     */
    public function recordQuery(string $pluginAlias, string $sql, float $time): void
    {
        $this->metrics['queries'][$pluginAlias][] = [
            'sql' => $sql,
            'time' => $time,
        ];
    }

    /**
     * Get compiled metrics report.
     */
    public function getReport(): array
    {
        return $this->metrics;
    }
}
