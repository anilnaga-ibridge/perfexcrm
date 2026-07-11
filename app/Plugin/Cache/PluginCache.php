<?php

namespace App\Plugin\Cache;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Class PluginCache
 * 
 * Evolved plugin cache manager enforcing key isolation via namespacing.
 */
class PluginCache
{
    protected CacheRepository $cache;
    protected string $prefix;

    public function __construct(CacheRepository $cache, string $pluginAlias)
    {
        $this->cache = $cache;
        $this->prefix = 'plugin_cache:' . strtolower($pluginAlias) . ':';
    }

    /**
     * Store an item in the cache.
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 3600): bool
    {
        return $this->cache->put($this->getNamespacedKey($key), $value, $ttlSeconds);
    }

    /**
     * Retrieve an item from the cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->getNamespacedKey($key), $default);
    }

    /**
     * Retrieve an item from the cache, or execute the given closure and store it.
     */
    public function remember(string $key, int $ttlSeconds, \Closure $callback): mixed
    {
        return $this->cache->remember($this->getNamespacedKey($key), $ttlSeconds, $callback);
    }

    /**
     * Determine if an item exists in the cache.
     */
    public function has(string $key): bool
    {
        return $this->cache->has($this->getNamespacedKey($key));
    }

    /**
     * Remove an item from the cache.
     */
    public function forget(string $key): bool
    {
        return $this->cache->forget($this->getNamespacedKey($key));
    }

    /**
     * Clear all cached keys mapped to this plugin namespace.
     * Note: We flush by tracking or by matching keys if using a taggable store,
     * or by clearing known prefixes. For simplicity, we expose clearing the namespace.
     */
    public function clear(): void
    {
        // Tag-based clearing or simple prefix management.
        // In default file/database store, we can use tags if supported,
        // otherwise cache values expire. We provide tags compatibility.
        if (method_exists($this->cache, 'tags')) {
            $this->cache->tags(['plugin_' . $this->prefix])->flush();
        }
    }

    /**
     * Namespace a key with the plugin prefix.
     */
    protected function getNamespacedKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
