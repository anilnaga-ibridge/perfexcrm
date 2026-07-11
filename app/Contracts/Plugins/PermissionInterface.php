<?php

namespace App\Contracts\Plugins;

/**
 * Interface PermissionInterface
 * 
 * Defines permission definitions registered by a plugin.
 */
interface PermissionInterface
{
    /**
     * Get the unique permission key name (e.g. "hrm_employee.view").
     */
    public function getKey(): string;

    /**
     * Get the human-readable description.
     */
    public function getDescription(): string;
}
