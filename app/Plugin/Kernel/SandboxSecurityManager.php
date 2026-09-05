<?php

namespace App\Plugin\Kernel;

class SandboxSecurityManager
{
    /**
     * Check if the plugin has declared permission for a specific scoped resource.
     *
     * @param PluginDescriptor $descriptor
     * @param string $permission (e.g. 'database.write', 'storage.write')
     * @throws \RuntimeException if permission is lacking.
     */
    public function checkPermission(PluginDescriptor $descriptor, string $permission): void
    {
        $permissions = $descriptor->capabilities['permissions'] ?? [];
        
        // Simple wildcard support: if plugin has '*' permission
        if (in_array('*', $permissions, true)) {
            return;
        }

        // Support scoped matches (e.g., 'database.*' matches 'database.write')
        foreach ($permissions as $p) {
            if ($p === $permission) {
                return;
            }
            if (str_ends_with($p, '.*')) {
                $prefix = substr($p, 0, -2);
                if (str_starts_with($permission, $prefix)) {
                    return;
                }
            }
        }

        throw new \RuntimeException("Plugin [{$descriptor->alias}] lacks declared permission: {$permission}");
    }
}
