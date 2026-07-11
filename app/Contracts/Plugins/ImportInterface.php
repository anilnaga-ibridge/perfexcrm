<?php

namespace App\Contracts\Plugins;

/**
 * Interface ImportInterface
 * 
 * Defines custom import handlers (CSV, Excel, API feeds) registered by plugins.
 */
interface ImportInterface
{
    /**
     * Get the accepted import formats (e.g. ["csv", "xlsx"]).
     */
    public function getAcceptedFormats(): array;

    /**
     * Execute the import process using the uploaded file path.
     */
    public function import(string $filePath): array;
}
