<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MissingFunctionRegistry
{
    /**
     * Map of dynamically mocked legacy functions.
     * These can be overridden or defined via hooks.
     */
    protected static $mockCallbacks = [];

    /**
     * Set a custom callback for a dynamic function name.
     */
    public static function register(string $name, callable $callback): void
    {
        self::$mockCallbacks[strtolower($name)] = $callback;
    }

    /**
     * Clear all registered dynamic functions.
     */
    public static function clear(): void
    {
        self::$mockCallbacks = [];
    }

    /**
     * Execute a dynamically stubbed legacy function.
     */
    public static function call(string $name, ...$args)
    {
        $lowerName = strtolower($name);

        if (isset(self::$mockCallbacks[$lowerName])) {
            return call_user_func_array(self::$mockCallbacks[$lowerName], $args);
        }

        // Default empty string fallback for missing UI/helper functions
        Log::debug("LEGACY COMPAT: stub function called [{$name}]");
        return '';
    }
}
