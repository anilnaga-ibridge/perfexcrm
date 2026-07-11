<?php

namespace App\Plugin\Registries;

use Illuminate\Contracts\Config\Repository as ConfigRepository;

/**
 * Class ConfigRegistry
 * 
 * Auto-merges and updates plugin configs into the central Laravel configuration repository.
 */
class ConfigRegistry
{
    /**
     * The Laravel config repository.
     */
    protected ConfigRepository $config;

    /**
     * ConfigRegistry constructor.
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * Merge plugin-specific configuration.
     */
    public function merge(string $key, array $values): void
    {
        $current = $this->config->get($key, []);
        $merged = array_replace_recursive($values, $current);
        $this->config->set($key, $merged);
    }

    /**
     * Set a configuration value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->config->set($key, $value);
    }

    /**
     * Get a configuration value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }
}
