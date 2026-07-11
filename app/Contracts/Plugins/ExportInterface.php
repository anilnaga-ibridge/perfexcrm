<?php

namespace App\Contracts\Plugins;

/**
 * Interface ExportInterface
 * 
 * Defines custom export handlers (PDF, CSV, Excel) registered by plugins.
 */
interface ExportInterface
{
    /**
     * Get the export file format type (e.g. "xlsx", "pdf", "csv").
     */
    public function getFormatType(): string;

    /**
     * Export the resource data to the given stream/file target.
     */
    public function export(array $data): string;
}
