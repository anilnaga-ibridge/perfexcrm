<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ModuleSettingsService
{
    private ModuleSettingValidator $validator;

    public function __construct(ModuleSettingValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Whether the module declares a settings schema in its manifest.
     */
    public function hasSettings(Module $module): bool
    {
        $manifest = $this->readManifest($module);
        return $manifest && isset($manifest['settings']['schema']);
    }

    /**
     * Get the parsed settings schema from the module's settings.json.
     */
    public function getSchema(Module $module): ?array
    {
        if (!$this->hasSettings($module)) {
            return null;
        }

        $cacheKey = "module.schema.{$module->alias}";

        return Cache::remember(
            $cacheKey,
            3600,
            function () use ($module) {
                $manifest = $this->readManifest($module);
                if (!$manifest || !isset($manifest['settings']['schema'])) {
                    return null;
                }
                $schemaPath = $this->modulePath($module) . '/' . $manifest['settings']['schema'];
                if (!File::exists($schemaPath)) {
                    return null;
                }
                $content = File::get($schemaPath);
                $decoded = json_decode($content, true);
                return is_array($decoded) ? $decoded : null;
            }
        );
    }

    /**
     * Get all persisted setting values for the module.
     * Keys without a persisted value but with a schema default are returned with the default.
     */
    public function getValues(Module $module): array
    {
        $rows = ModuleSetting::where('module_id', $module->id)->get()->keyBy('setting_key');
        $values = [];

        $schema = $this->getSchema($module);
        if ($schema) {
            foreach ($this->flattenFields($schema) as $field) {
                $key = $field['key'];
                if ($rows->has($key)) {
                    $values[$key] = $this->decodeValue($rows->get($key)->setting_value, $field['type'] ?? 'text');
                } elseif (array_key_exists('default', $field)) {
                    $values[$key] = $field['default'];
                }
            }
        }

        return $values;
    }

    /**
     * Get the schema and current values in a single response.
     */
    public function getSettings(Module $module): ?array
    {
        $schema = $this->getSchema($module);
        if ($schema === null) {
            return null;
        }

        return [
            'schema' => $schema,
            'values' => $this->getValues($module),
        ];
    }

    /**
     * Check whether a specific setting key has a persisted value
     * (as opposed to only a schema default).
     */
    public function hasValue(Module $module, string $key): bool
    {
        return ModuleSetting::where('module_id', $module->id)
            ->where('setting_key', $key)
            ->exists();
    }

    /**
     * Validate and persist setting values.
     * Only submitted keys are updated; missing keys are left unchanged.
     */
    public function save(Module $module, array $values): array
    {
        $schema = $this->getSchema($module);
        if ($schema === null) {
            throw new \RuntimeException("Module '{$module->alias}' has no settings schema.");
        }

        $validated = $this->validator->validate($schema, $values);

        DB::transaction(function () use ($module, $schema, $validated) {
            foreach ($validated as $key => $value) {
                $fieldType = $this->fieldType($schema, $key) ?? 'text';
                ModuleSetting::updateOrCreate(
                    ['module_id' => $module->id, 'setting_key' => $key],
                    ['setting_value' => $this->encodeValue($value, $fieldType)]
                );
            }
        });

        Cache::forget("module.settings.{$module->alias}");

        return $this->getValues($module);
    }

    /**
     * Delete all persisted settings for the module, reverting to schema defaults.
     */
    public function resetToDefaults(Module $module): array
    {
        ModuleSetting::where('module_id', $module->id)->delete();
        Cache::forget("module.settings.{$module->alias}");

        return $this->getValues($module);
    }

    // ─── Private helpers ──────────────────────────────────────

    private function modulePath(Module $module): string
    {
        $path = base_path("Modules/{$module->alias}");
        if (is_dir($path)) {
            return $path;
        }
        $legacyPath = base_path("Modules/{$module->name}");
        if (is_dir($legacyPath)) {
            return $legacyPath;
        }
        return $path;
    }

    private function readManifest(Module $module): ?array
    {
        $manifestPath = $this->modulePath($module) . '/module.json';
        if (!File::exists($manifestPath)) {
            return null;
        }
        $content = File::get($manifestPath);
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function flattenFields(array $schema): array
    {
        $fields = [];
        foreach ($schema['sections'] ?? [] as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    private function fieldType(array $schema, string $key): ?string
    {
        foreach ($this->flattenFields($schema) as $field) {
            if ($field['key'] === $key) {
                return $field['type'] ?? 'text';
            }
        }
        return null;
    }

    private function encodeValue(mixed $value, string $type): string
    {
        if ($type === 'multiselect' && is_array($value)) {
            return json_encode($value);
        }
        if ($type === 'password') {
            return encrypt((string) $value);
        }
        return (string) $value;
    }

    private function decodeValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($type === 'multiselect') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        if ($type === 'number') {
            return is_numeric($value) ? (float) $value : $value;
        }
        if ($type === 'boolean') {
            return in_array($value, ['1', 1, 'true', true], true);
        }
        if ($type === 'password') {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    /**
     * Invalidate schema cache — called on module install/upgrade/uninstall.
     */
    public static function flushSchemaCache(Module $module): void
    {
        Cache::forget("module.schema.{$module->alias}");
    }
}
