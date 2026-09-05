<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Loads CodeIgniter-style language files from module directories.
 * CI pattern: Modules/{alias}/language/{lang}/{file}_lang.php
 * populates global $lang array.
 */
class CILanguageLoader
{
    /** Cache loaded language arrays to avoid re-scanning files per request */
    protected static $loadedLanguages = [];

    /**
     * Try to resolve a language key by scanning all active module language files.
     * Returns the translated string or null if not found.
     */
    public static function resolve(string $key): ?string
    {
        global $lang;

        // If key already exists in $lang (loaded earlier), return it
        if (isset($lang[$key])) {
            return $lang[$key];
        }

        // Scan all active modules for language files
        $activeModules = \App\Models\Module::where('status', 'active')->get();
        $locale = self::getLocale();

        foreach ($activeModules as $mod) {
            $alias = $mod->alias ?? $mod['alias'] ?? '';
            self::loadModuleLanguage($alias, $locale);

            // Check again after loading
            if (isset($lang[$key])) {
                return $lang[$key];
            }
        }

        return null;
    }

    /**
     * Load a specific module's language file into the global $lang array.
     */
    public static function loadModuleLanguage(string $alias, string $locale = 'english'): void
    {
        global $lang;

        $cacheKey = "{$alias}_{$locale}";
        if (isset(self::$loadedLanguages[$cacheKey])) {
            return;
        }
        self::$loadedLanguages[$cacheKey] = true;

        $modulePath = base_path("Modules/{$alias}");
        $langDir = "{$modulePath}/language/{$locale}";

        if (!is_dir($langDir)) {
            // Try fallback to english
            $langDir = "{$modulePath}/language/english";
            if (!is_dir($langDir)) {
                return;
            }
        }

        // Load all *_lang.php files in the language directory
        $langFiles = glob("{$langDir}/*_lang.php");
        foreach ($langFiles as $langFile) {
            // Temporarily suppress errors for malformed lang files
            try {
                // CI lang files populate $lang array
                include $langFile;
            } catch (\Throwable $e) {
                // Skip broken lang files
            }
        }
    }

    /**
     * Get the current locale (maps Laravel locale to CI locale directory name).
     */
    protected static function getLocale(): string
    {
        $locale = app()->getLocale() ?? config('app.locale', 'english');

        // Map common Laravel locales to CI directory names
        $map = [
            'en' => 'english',
            'en-US' => 'english',
            'fr' => 'french',
            'fr-FR' => 'french',
            'de' => 'german',
            'de-DE' => 'german',
            'es' => 'spanish',
            'es-ES' => 'spanish',
            'pt' => 'portuguese',
            'pt-BR' => 'portuguese_br',
            'nl' => 'dutch',
            'nl-NL' => 'dutch',
            'it' => 'italian',
            'it-IT' => 'italian',
            'ru' => 'russian',
            'ru-RU' => 'russian',
            'zh' => 'chinese',
            'zh-CN' => 'chinese',
            'ja' => 'japanese',
            'ja-JP' => 'japanese',
            'pl' => 'polish',
            'pl-PL' => 'polish',
            'tr' => 'turkish',
            'tr-TR' => 'turkish',
            'uk' => 'ukrainian',
            'uk-UA' => 'ukrainian',
            'sv' => 'swedish',
            'sv-SE' => 'swedish',
            'cs' => 'czech',
            'cs-CZ' => 'czech',
            'el' => 'greek',
            'el-GR' => 'greek',
            'ro' => 'romanian',
            'ro-RO' => 'romanian',
            'bg' => 'bulgarian',
            'bg-BG' => 'bulgarian',
            'sk' => 'slovak',
            'sk-SK' => 'slovak',
            'ca' => 'catalan',
            'ca-ES' => 'catalan',
            'id' => 'indonesia',
            'id-ID' => 'indonesia',
            'fa' => 'persian',
            'fa-IR' => 'persian',
            'vi' => 'vietnamese',
            'vi-VN' => 'vietnamese',
        ];

        return $map[$locale] ?? $locale;
    }
}
