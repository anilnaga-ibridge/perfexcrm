<?php

namespace App\Services\Validation;

use Illuminate\Support\Facades\File;

class ModuleContext
{
    private string $path;
    private string $alias;
    private ?array $manifest = null;

    public function __construct(string $path, string $alias)
    {
        $this->path = rtrim($path, '/');
        $this->alias = $alias;
    }

    /**
     * Get the absolute filesystem path to the module.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the expected module alias.
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Check if a file exists relative to the module root.
     */
    public function hasFile(string $relativePath): bool
    {
        return File::exists($this->path . '/' . ltrim($relativePath, '/'));
    }

    /**
     * Check if a directory exists relative to the module root.
     */
    public function hasDirectory(string $relativePath): bool
    {
        return is_dir($this->path . '/' . ltrim($relativePath, '/'));
    }

    /**
     * Get the text content of a file relative to the module root.
     */
    public function getFileContent(string $relativePath): ?string
    {
        $fullPath = $this->path . '/' . ltrim($relativePath, '/');
        if (!File::exists($fullPath)) {
            return null;
        }
        return File::get($fullPath);
    }

    /**
     * Get and decode a JSON file relative to the module root.
     */
    public function getJsonFile(string $relativePath): ?array
    {
        $content = $this->getFileContent($relativePath);
        if ($content === null) {
            return null;
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get the parsed manifest (module.json) data.
     */
    public function getManifest(): ?array
    {
        if ($this->manifest === null) {
            $this->manifest = $this->getJsonFile('module.json') ?: [];
        }
        return $this->manifest;
    }
}
