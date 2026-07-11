<?php

namespace App\Contracts\Plugins;

/**
 * Interface SearchInterface
 * 
 * Defines how plugins hook into global search query execution and formatting.
 */
interface SearchInterface
{
    /**
     * Get the display name of the searchable resource group (e.g. "Employees").
     */
    public function getResourceName(): string;

    /**
     * Execute search query and return formatted search hits.
     * Each search hit must have: title, description, link/route, metadata.
     */
    public function search(string $query, int $limit = 10): array;
}
