<?php

namespace App\Plugin\Registries;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Plugin\PluginInstance;
use App\Services\ModuleManager;
use App\Services\ModuleValidator;

/**
 * Class PluginRegistry
 * 
 * Evolved Registry service that discovers, loads, and tracks all modules and their providers.
 */
class PluginRegistry
{
    /**
     * Cache of discovered PluginInstance objects.
     */
    protected array $plugins = [];

    /**
     * Registered service provider class names mapped to plugin aliases.
     */
    protected array $providers = [];

    /**
     * PluginRegistry constructor.
     */
    public function __construct()
    {
        $this->discover();
    }

    /**
     * Discover all plugins located in the Modules/ directory.
     */
    public function discover(): void
    {
        $this->plugins = [];
        $modulesDir = base_path('Modules');
        if (!File::exists($modulesDir)) {
            return;
        }

        // Fetch status mapping from the database safely
        $statusMapping = [];
        try {
            if (class_exists(Schema::class) && Schema::hasTable('modules')) {
                $statusMapping = DB::table('modules')
                    ->pluck('status', 'alias')
                    ->toArray();
            }
        } catch (\Throwable $e) {
            // Ignore database connection failures during early bootstrap/testing
        }

        $dirs = File::directories($modulesDir);
        foreach ($dirs as $dir) {
            $alias = basename($dir);
            $manifestPath = $dir . '/module.json';
            $info = null;

            if (File::exists($manifestPath)) {
                $content = File::get($manifestPath);
                $info = json_decode($content, true);
            } else {
                // Fallback to legacy Perfex/CodeIgniter style PHP file comment parsing
                $files = File::files($dir);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $parsed = $this->parseLegacyPhpHeaders($file->getPathname());
                        if ($parsed) {
                            $info = $parsed;
                            break;
                        }
                    }
                }
            }

            if ($info && is_array($info)) {
                $alias = $info['alias'] ?? $alias;
                $normalizedAlias = ModuleValidator::normalizeAlias($alias);
                
                // Determine namespace prefix
                $namespace = $this->aliasToNamespace($normalizedAlias);
                $status = $statusMapping[$normalizedAlias] ?? 'installed';

                $this->plugins[$normalizedAlias] = new PluginInstance([
                    'name' => $info['name'] ?? $normalizedAlias,
                    'alias' => $normalizedAlias,
                    'version' => $info['version'] ?? '1.0.0',
                    'namespace' => "Modules\\{$namespace}\\",
                    'path' => $dir,
                    'status' => $status,
                    'depends' => $info['depends'] ?? [],
                    'minimum_core_version' => $info['minimum_core_version'] ?? '1.0.0',
                ]);

                // Auto-discover Plugin Service Providers inside Modules/{Plugin}/Providers/
                $providersDir = $dir . '/Providers';
                if (File::isDirectory($providersDir)) {
                    $provFiles = File::files($providersDir);
                    foreach ($provFiles as $provFile) {
                        if ($provFile->getExtension() === 'php' && str_ends_with($provFile->getFilename(), 'ServiceProvider.php')) {
                            $className = $provFile->getBasename('.php');
                            $fullProviderClass = "Modules\\{$namespace}\\Providers\\{$className}";
                            $this->providers[$normalizedAlias] = $fullProviderClass;
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all discovered plugins.
     * 
     * @return PluginInstance[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get only active plugins.
     * 
     * @return PluginInstance[]
     */
    public function getActivePlugins(): array
    {
        return array_filter($this->plugins, fn($p) => $p->isActive());
    }

    /**
     * Find a plugin by its alias.
     */
    public function getPlugin(string $alias): ?PluginInstance
    {
        $normalized = ModuleValidator::normalizeAlias($alias);
        return $this->plugins[$normalized] ?? null;
    }

    /**
     * Get all auto-discovered Service Providers.
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get service provider class for a specific active plugin.
     */
    public function getProviderFor(string $alias): ?string
    {
        $normalized = ModuleValidator::normalizeAlias($alias);
        return $this->providers[$normalized] ?? null;
    }

    /**
     * Parse WordPress/Perfex style comment headers from legacy PHP files.
     */
    protected function parseLegacyPhpHeaders(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        if (!preg_match('/Module Name:\s*([^\r\n]+)/i', $content, $nameMatches)) {
            return null;
        }

        $name = trim($nameMatches[1]);
        $alias = strtolower(str_replace(' ', '-', $name));
        $version = '1.0.0';
        $author = 'System';

        if (preg_match('/Version:\s*([^\r\n]+)/i', $content, $verMatches)) {
            $version = trim($verMatches[1]);
        }
        if (preg_match('/Author:\s*([^\r\n]+)/i', $content, $authMatches)) {
            $author = trim($authMatches[1]);
        }

        return [
            'name' => $name,
            'alias' => $alias,
            'version' => $version,
            'author' => $author,
            'depends' => [],
        ];
    }

    /**
     * Helper to format alias as PascalCase.
     */
    protected function aliasToNamespace(string $value): string
    {
        $namespace = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
        if (preg_match('/^[0-9]/', $namespace)) {
            $namespace = 'Mod_' . $namespace;
        }
        return $namespace;
    }
}
