<?php

namespace App\Plugin\Registries;

use App\Contracts\Plugins\ExportInterface;

/**
 * Class ExportRegistry
 * 
 * Stores data export providers registered by plugins.
 */
class ExportRegistry
{
    /**
     * Registered export providers.
     * 
     * @var ExportInterface[]
     */
    protected array $providers = [];

    /**
     * Register a new export provider.
     */
    public function register(ExportInterface $provider): void
    {
        $this->providers[$provider->getFormatType()][] = $provider;
    }

    /**
     * Get all export providers.
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Get export providers for a specific format type.
     */
    public function getForFormat(string $formatType): array
    {
        return $this->providers[$formatType] ?? [];
    }
}
