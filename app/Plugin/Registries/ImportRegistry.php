<?php

namespace App\Plugin\Registries;

use App\Contracts\Plugins\ImportInterface;

/**
 * Class ImportRegistry
 * 
 * Stores data import providers registered by plugins.
 */
class ImportRegistry
{
    /**
     * Registered import providers.
     * 
     * @var ImportInterface[]
     */
    protected array $providers = [];

    /**
     * Register a new import provider.
     */
    public function register(ImportInterface $provider): void
    {
        foreach ($provider->getAcceptedFormats() as $format) {
            $this->providers[strtolower($format)][] = $provider;
        }
    }

    /**
     * Get all import providers.
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Get import providers for a specific file extension/format.
     */
    public function getForFormat(string $format): array
    {
        return $this->providers[strtolower($format)] ?? [];
    }
}
