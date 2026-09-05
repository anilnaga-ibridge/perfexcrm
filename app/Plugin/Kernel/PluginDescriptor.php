<?php

namespace App\Plugin\Kernel;

final class PluginDescriptor
{
    public readonly string $name;
    public readonly string $alias;
    public readonly string $version;
    public readonly string $sdkVersion;
    public readonly string $apiVersion;
    public readonly string $minimumPlatform;
    public readonly string $checksum;
    public readonly string $state;
    public readonly array $dependencies;
    public readonly array $capabilities;
    public readonly array $services;

    public function __construct(array $manifest, string $checksum = '')
    {
        $this->name = $manifest['name'] ?? '';
        $this->alias = $manifest['alias'] ?? '';
        $this->version = $manifest['version'] ?? '1.0.0';
        $this->sdkVersion = $manifest['sdkVersion'] ?? '2.0';
        $this->apiVersion = $manifest['apiVersion'] ?? '1';
        $this->minimumPlatform = $manifest['minimumPlatform'] ?? '1.0.0';
        $this->checksum = $checksum;
        $this->state = 'Verified';
        
        $this->dependencies = $manifest['dependencies'] ?? [];
        $this->capabilities = $manifest['capabilities'] ?? [];
        $this->services = $manifest['services'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'alias' => $this->alias,
            'version' => $this->version,
            'sdkVersion' => $this->sdkVersion,
            'apiVersion' => $this->apiVersion,
            'minimumPlatform' => $this->minimumPlatform,
            'checksum' => $this->checksum,
            'state' => $this->state,
            'dependencies' => $this->dependencies,
            'capabilities' => $this->capabilities,
            'services' => $this->services,
        ];
    }
}
