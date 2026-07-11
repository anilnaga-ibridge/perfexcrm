<?php

namespace App\Plugin\Registries;

use App\Contracts\Plugins\SearchInterface;

/**
 * Class SearchRegistry
 * 
 * Manages search providers registered by plugins to hook into global search.
 */
class SearchRegistry
{
    /**
     * Registered search provider instances.
     * 
     * @var SearchInterface[]
     */
    protected array $providers = [];

    /**
     * Register a new searchable resource provider.
     */
    public function register(SearchInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Query all registered search providers and aggregate matches.
     */
    public function query(string $query, int $limitPerProvider = 10): array
    {
        $results = [];
        foreach ($this->providers as $provider) {
            try {
                $hits = $provider->search($query, $limitPerProvider);
                if (!empty($hits)) {
                    $results[$provider->getResourceName()] = $hits;
                }
            } catch (\Throwable $e) {
                // Fail-safe execution: prevent faulty search providers from crashing global search
            }
        }
        return $results;
    }

    /**
     * Get all registered search providers.
     */
    public function all(): array
    {
        return $this->providers;
    }
}
