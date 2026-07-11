<?php

namespace App\Plugin\Logging;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class PluginAuditLogger
 * 
 * Records system changes and lifecycle transactions in a dedicated database log.
 */
class PluginAuditLogger
{
    /**
     * Log a plugin lifecycle transaction.
     */
    public function log(string $pluginAlias, string $action, string $status, ?string $message = null): void
    {
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_audit_logs')) {
                DB::table('module_audit_logs')->insert([
                    'module_alias' => strtolower($pluginAlias),
                    'action' => $action,
                    'status' => $status,
                    'message' => $message,
                    'user_id' => auth()->id() ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
    }

    /**
     * Get transaction logs.
     */
    public function getHistory(string $pluginAlias): array
    {
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_audit_logs')) {
                return DB::table('module_audit_logs')
                    ->where('module_alias', strtolower($pluginAlias))
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
        return [];
    }
}
