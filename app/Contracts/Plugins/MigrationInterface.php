<?php

namespace App\Contracts\Plugins;

/**
 * Interface MigrationInterface
 * 
 * Defines basic setup and rollback schema methods for plugins.
 */
interface MigrationInterface
{
    /**
     * Run the schema modifications (installing tables, columns, settings).
     */
    public function up(): void;

    /**
     * Revert the schema modifications (dropping tables, columns, settings).
     */
    public function down(): void;
}
