<?php

namespace App\Plugin\Assets;

use App\Contracts\Plugins\AssetInterface;
use Illuminate\Support\Facades\File;

/**
 * Class AssetPipeline
 * 
 * Evolved asset pipeline that publishes static files and merges Vite manifests for plugins.
 */
class AssetPipeline
{
    /**
     * Cache of registered assets.
     */
    protected array $assets = [];

    /**
     * Register a new plugin asset provider.
     */
    public function register(AssetInterface $asset): void
    {
        $this->assets[$asset->getPublishAlias()] = $asset;
        $this->publish($asset);
    }

    /**
     * Publish plugin asset directory to public folder.
     */
    public function publish(AssetInterface $asset): void
    {
        $sourcePath = $asset->getSourcePath();
        if (!File::isDirectory($sourcePath)) {
            return;
        }

        $publishAlias = strtolower($asset->getPublishAlias());
        $targetPath = public_path("modules/{$publishAlias}");

        File::ensureDirectoryExists(dirname($targetPath));

        // Delete old target path to publish fresh copy
        if (File::exists($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        File::copyDirectory($sourcePath, $targetPath);

        // Merge Vite manifests if a manifest is present in the published assets
        $this->mergeViteManifest($publishAlias, $targetPath);
    }

    /**
     * Merge plugin Vite manifests into the central public/build/manifest.json file.
     */
    protected function mergeViteManifest(string $alias, string $publishDir): void
    {
        $candidateManifests = [
            $publishDir . '/manifest.json',
            $publishDir . '/build/manifest.json',
        ];

        $pluginManifestPath = null;
        foreach ($candidateManifests as $path) {
            if (File::exists($path)) {
                $pluginManifestPath = $path;
                break;
            }
        }

        if (!$pluginManifestPath) {
            return;
        }

        $mainManifestPath = public_path('build/manifest.json');
        if (!File::exists($mainManifestPath)) {
            return;
        }

        try {
            $mainManifest = json_decode(File::get($mainManifestPath), true) ?? [];
            $pluginManifest = json_decode(File::get($pluginManifestPath), true) ?? [];

            // Merge entries with namespaced keys to avoid conflicts
            foreach ($pluginManifest as $key => $value) {
                // Adjust asset paths relative to the public root
                if (isset($value['file'])) {
                    $value['file'] = "modules/{$alias}/" . (str_starts_with($value['file'], 'build/') ? substr($value['file'], 6) : $value['file']);
                }
                if (isset($value['css'])) {
                    foreach ($value['css'] as $idx => $cssPath) {
                        $value['css'][$idx] = "modules/{$alias}/" . (str_starts_with($cssPath, 'build/') ? substr($cssPath, 6) : $cssPath);
                    }
                }
                
                $namespacedKey = "modules/{$alias}/{$key}";
                $mainManifest[$namespacedKey] = $value;
            }

            File::put($mainManifestPath, json_encode($mainManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            // Fail-safe compilation merging
        }
    }

    /**
     * Get all registered asset providers.
     */
    public function all(): array
    {
        return $this->assets;
    }
}
