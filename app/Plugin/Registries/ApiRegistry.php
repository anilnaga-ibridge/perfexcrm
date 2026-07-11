<?php

namespace App\Plugin\Registries;

/**
 * Class ApiRegistry
 * 
 * Stores endpoints registered by plugins (REST, GraphQL, RPC, Webhooks).
 */
class ApiRegistry
{
    protected array $apis = [];

    /**
     * Register a new API endpoint.
     */
    public function register(string $pluginAlias, string $type, array $config): void
    {
        $this->apis[strtolower($pluginAlias)][$type][] = $config;
    }

    /**
     * Get registered APIs for a plugin.
     */
    public function getApis(string $pluginAlias): array
    {
        return $this->apis[strtolower($pluginAlias)] ?? [];
    }

    /**
     * Get all registered APIs.
     */
    public function all(): array
    {
        return $this->apis;
    }
}
