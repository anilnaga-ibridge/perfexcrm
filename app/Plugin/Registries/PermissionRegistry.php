<?php

namespace App\Plugin\Registries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class PermissionRegistry
 * 
 * Manages plugin-owned permissions, tracking ownership mapping, and enabling dynamic uninstall rollbacks.
 */
class PermissionRegistry
{
    /**
     * Map a permission name to a plugin alias.
     */
    public function registerOwnership(string $pluginAlias, string $permissionName): void
    {
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_permissions')) {
                // Ensure module record exists to link to
                $module = DB::table('modules')->where('alias', $pluginAlias)->first();
                if ($module) {
                    DB::table('module_permissions')->insertOrIgnore([
                        'module_id' => $module->id,
                        'permission_name' => $permissionName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
    }

    /**
     * Clean up all permissions owned by the given plugin from the system.
     */
    public function purge(string $pluginAlias): void
    {
        $alias = strtolower($pluginAlias);
        try {
            if (class_exists(Schema::class) && Schema::hasTable('modules')) {
                $module = DB::table('modules')->where('alias', $alias)->first();
                if ($module) {
                    $permissions = DB::table('module_permissions')
                        ->where('module_id', $module->id)
                        ->pluck('permission_name')
                        ->toArray();

                    if (!empty($permissions)) {
                        // Drop role bindings
                        $permIds = DB::table('permissions')
                            ->whereIn('name', $permissions)
                            ->pluck('id')
                            ->toArray();

                        if (!empty($permIds)) {
                            DB::table('role_permissions')->whereIn('permission_id', $permIds)->delete();
                            DB::table('permissions')->whereIn('id', $permIds)->delete();
                        }
                    }

                    // Delete the module permissions mapping
                    DB::table('module_permissions')->where('module_id', $module->id)->delete();
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
    }

    /**
     * Generate a report of all registered permissions mapped by owner.
     */
    public function getReport(): array
    {
        $report = [];
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_permissions')) {
                $rows = DB::table('module_permissions')
                    ->join('modules', 'module_permissions.module_id', '=', 'modules.id')
                    ->select('modules.alias', 'module_permissions.permission_name')
                    ->get();

                foreach ($rows as $row) {
                    $report[$row->alias][] = $row->permission_name;
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
        return $report;
    }
}
