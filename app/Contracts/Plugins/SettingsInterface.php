<?php

namespace App\Contracts\Plugins;

/**
 * Interface SettingsInterface
 * 
 * Defines the database or memory configuration schema supplied by a plugin.
 */
interface SettingsInterface
{
    /**
     * Get the settings group key.
     */
    public function getGroup(): string;

    /**
     * Get the list of configuration fields and types.
     */
    public function getFields(): array;
}
