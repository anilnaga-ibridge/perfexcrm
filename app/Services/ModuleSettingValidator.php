<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator as LaravelValidator;

class ModuleSettingValidator
{
    /**
     * Validate submitted values against the schema.
     *
     * @param array $schema Parsed settings.json
     * @param array $values Key-value pairs submitted by the user
     * @return array Validated key-value pairs
     * @throws \InvalidArgumentException on first validation failure
     */
    public function validate(array $schema, array $values): array
    {
        $fields = $this->flattenFields($schema);
        $validated = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $hasValue = array_key_exists($key, $values);
            $value = $hasValue ? $values[$key] : null;
            $rules = $field['validation'] ?? [];
            $type = $field['type'] ?? 'text';

            // Required check
            if (!empty($rules['required']) && (!$hasValue || $value === '' || $value === null)) {
                throw new \InvalidArgumentException("{$key} is required.");
            }

            // Skip further validation if not present and not required
            if (!$hasValue || $value === null || $value === '') {
                continue;
            }

            // Type-specific validation
            $this->validateType($key, $value, $type, $rules);

            // Store coerced value
            $validated[$key] = $this->coerceValue($value, $type);
        }

        return $validated;
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

    private function validateType(string $key, mixed $value, string $type, array $rules): void
    {
        switch ($type) {
            case 'number':
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException("{$key} must be a number.");
                }
                $num = (float) $value;
                if (isset($rules['min']) && $num < $rules['min']) {
                    throw new \InvalidArgumentException("{$key} must be at least {$rules['min']}.");
                }
                if (isset($rules['max']) && $num > $rules['max']) {
                    throw new \InvalidArgumentException("{$key} must be at most {$rules['max']}.");
                }
                break;

            case 'boolean':
                if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                    throw new \InvalidArgumentException("{$key} must be a boolean.");
                }
                break;

            case 'select':
                if (isset($rules['options']) && is_array($rules['options'])) {
                    if (!in_array($value, $rules['options'], true)) {
                        throw new \InvalidArgumentException("{$key} must be one of: " . implode(', ', $rules['options']) . ".");
                    }
                }
                break;

            case 'multiselect':
                if (!is_array($value)) {
                    throw new \InvalidArgumentException("{$key} must be an array.");
                }
                if (isset($rules['options']) && is_array($rules['options'])) {
                    foreach ($value as $v) {
                        if (!in_array($v, $rules['options'], true)) {
                            throw new \InvalidArgumentException("{$key} contains invalid value '{$v}'.");
                        }
                    }
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException("{$key} must be a valid email address.");
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new \InvalidArgumentException("{$key} must be a valid URL.");
                }
                break;

            case 'password':
            case 'text':
            case 'textarea':
            case 'color':
                $strValue = (string) $value;
                if (isset($rules['min_length']) && mb_strlen($strValue) < $rules['min_length']) {
                    throw new \InvalidArgumentException("{$key} must be at least {$rules['min_length']} characters.");
                }
                if (isset($rules['max_length']) && mb_strlen($strValue) > $rules['max_length']) {
                    throw new \InvalidArgumentException("{$key} must be at most {$rules['max_length']} characters.");
                }
                if (isset($rules['pattern']) && !preg_match($rules['pattern'], $strValue)) {
                    throw new \InvalidArgumentException("{$key} format is invalid.");
                }
                break;
        }
    }

    private function coerceValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'number' => is_numeric($value) ? (float) $value : $value,
            'boolean' => in_array($value, [true, 1, '1', 'true'], true),
            'multiselect' => is_array($value) ? $value : (json_decode($value, true) ?? []),
            'password', 'text', 'textarea', 'color', 'select', 'email', 'url' => (string) $value,
            default => $value,
        };
    }
}
