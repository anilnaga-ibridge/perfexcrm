<?php

namespace App\Plugin\Runtime;

/**
 * Class PluginRuntime
 * 
 * Manages the loaded runtime state of active plugins and gathers execution metrics.
 */
class PluginRuntime
{
    protected array $loadedProviders = [];
    protected array $timings = [];
    protected array $memory = [];
    protected array $diagnostics = [];

    /**
     * Start profiling a plugin event or boot stage.
     */
    public function startMetric(string $key): void
    {
        $this->timings[$key]['start'] = microtime(true);
        $this->memory[$key]['start'] = memory_get_usage();
    }

    /**
     * Stop profiling a plugin event or boot stage.
     */
    public function stopMetric(string $key): void
    {
        if (isset($this->timings[$key]['start'])) {
            $this->timings[$key]['end'] = microtime(true);
            $this->timings[$key]['duration'] = $this->timings[$key]['end'] - $this->timings[$key]['start'];
        }

        if (isset($this->memory[$key]['start'])) {
            $this->memory[$key]['end'] = memory_get_usage();
            $this->memory[$key]['diff'] = $this->memory[$key]['end'] - $this->memory[$key]['start'];
        }
    }

    /**
     * Get all execution timings.
     */
    public function getTimings(): array
    {
        return $this->timings;
    }

    /**
     * Get memory usage differentials.
     */
    public function getMemory(): array
    {
        return $this->memory;
    }

    /**
     * Log a runtime diagnostic message.
     */
    public function logDiagnostic(string $level, string $message): void
    {
        $this->diagnostics[] = [
            'timestamp' => microtime(true),
            'level' => $level,
            'message' => $message,
        ];
    }

    /**
     * Get all gathered diagnostics.
     */
    public function getDiagnostics(): array
    {
        return $this->diagnostics;
    }

    /**
     * Track a registered service provider class.
     */
    public function trackProvider(string $providerClass): void
    {
        if (!in_array($providerClass, $this->loadedProviders)) {
            $this->loadedProviders[] = $providerClass;
        }
    }

    /**
     * Get list of loaded service providers.
     */
    public function getLoadedProviders(): array
    {
        return $this->loadedProviders;
    }
}
