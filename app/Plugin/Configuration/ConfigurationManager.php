<?php

namespace App\Plugin\Configuration;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class ConfigurationManager
 * 
 * Manages plugin configuration merge precedence, runtime overrides, 
 * database-level settings overlays, and encryption for sensitive credentials.
 */
class ConfigurationManager
{
    /**
     * Map of plugin configurations cached in memory.
     */
    protected array $configs = [];

    /**
     * Precedence:
     * 1. Default Config (Laravel / core defaults)
     * 2. Plugin Config (defined by plugin service provider)
     * 3. Environment Variables (using env('PLUGIN_XYZ'))
     * 4. Database Settings (loaded from dynamic database settings table)
     * 5. Runtime Overrides (set in memory at runtime)
     */
    public function get(string $pluginAlias, string $key, mixed $default = null): mixed
    {
        $alias = strtolower($pluginAlias);
        $fullKey = "{$alias}.{$key}";

        // 1. Runtime override check
        if (isset($this->configs[$fullKey])) {
            return $this->configs[$fullKey];
        }

        // 2. Database settings check
        $dbVal = $this->getFromDatabase($alias, $key);
        if ($dbVal !== null) {
            return $dbVal;
        }

        // 3. Environment override check (e.g. PLUGIN_PAYROLL_API_KEY)
        $envKey = 'PLUGIN_' . strtoupper(str_replace('-', '_', $alias)) . '_' . strtoupper(str_replace('.', '_', $key));
        $envVal = env($envKey);
        if ($envVal !== null) {
            return $envVal;
        }

        // 4. Default Laravel configuration repository fallback
        return config($fullKey, $default);
    }

    /**
     * Set configuration override in memory for the current request.
     */
    public function set(string $pluginAlias, string $key, mixed $value): void
    {
        $alias = strtolower($pluginAlias);
        $fullKey = "{$alias}.{$key}";
        $this->configs[$fullKey] = $value;
        config([$fullKey => $value]);
    }

    /**
     * Save configuration settings to the database.
     */
    public function saveToDatabase(string $pluginAlias, string $key, mixed $value, bool $encrypt = false): void
    {
        $alias = strtolower($pluginAlias);
        $dbValue = $value;

        if ($encrypt && $value !== null) {
            $dbValue = Crypt::encryptString((string)$value);
        }

        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_settings')) {
                // Check if setting exists
                $exists = DB::table('module_settings')
                    ->where('module_alias', $alias)
                    ->where('key_name', $key)
                    ->exists();

                if ($exists) {
                    DB::table('module_settings')
                        ->where('module_alias', $alias)
                        ->where('key_name', $key)
                        ->update([
                            'value' => json_encode($dbValue),
                            'is_encrypted' => $encrypt,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('module_settings')->insert([
                        'module_alias' => $alias,
                        'key_name' => $key,
                        'value' => json_encode($dbValue),
                        'is_encrypted' => $encrypt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Clear config cache for this setting
                Cache::forget($this->getCacheKey($alias, $key));
            }
        } catch (\Throwable $e) {
            // Fail-safe during early boots or tests
        }
    }

    /**
     * Get dynamic setting value from the database with caching.
     */
    protected function getFromDatabase(string $alias, string $key): mixed
    {
        $cacheKey = $this->getCacheKey($alias, $key);

        return Cache::remember($cacheKey, 3600, function () use ($alias, $key) {
            try {
                if (class_exists(Schema::class) && Schema::hasTable('module_settings')) {
                    $row = DB::table('module_settings')
                        ->where('module_alias', $alias)
                        ->where('key_name', $key)
                        ->first();

                    if ($row) {
                        $value = json_decode($row->value, true);
                        if ($row->is_encrypted && $value !== null) {
                            return Crypt::decryptString($value);
                        }
                        return $value;
                    }
                }
            } catch (\Throwable $e) {
                // Fail-safe
            }
            return null;
        });
    }

    /**
     * Get unique cache key for configuration tags.
     */
    protected function getCacheKey(string $alias, string $key): string
    {
        return "plugin_config_cache:{$alias}:{$key}";
    }

    /**
     * Clear all cached settings for a plugin.
     */
    public function clearCache(string $pluginAlias): void
    {
        $alias = strtolower($pluginAlias);
        try {
            if (class_exists(Schema::class) && Schema::hasTable('module_settings')) {
                $keys = DB::table('module_settings')
                    ->where('module_alias', $alias)
                    ->pluck('key_name')
                    ->toArray();

                foreach ($keys as $key) {
                    Cache::forget($this->getCacheKey($alias, $key));
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe
        }
    }
}
