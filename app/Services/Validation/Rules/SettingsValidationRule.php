<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class SettingsValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Settings';
    }

    public function weight(): int
    {
        return 10;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $manifest = $context->getManifest();
        $schemaFile = $manifest['settings']['schema'] ?? null;

        if ($schemaFile === null) {
            $result->addInfo("No settings schema specified in manifest.");
            return $result;
        }

        if (!$context->hasFile($schemaFile)) {
            $result->addError("Settings schema file '{$schemaFile}' specified in manifest is missing.");
            return $result;
        }

        $schema = $context->getJsonFile($schemaFile);
        if ($schema === null) {
            $result->addError("Settings schema '{$schemaFile}' contains invalid JSON.");
            return $result;
        }

        if (!isset($schema['sections']) || !is_array($schema['sections'])) {
            $result->addError("Settings schema must contain a 'sections' array.");
            return $result;
        }

        $validTypes = [
            'text', 'textarea', 'email', 'url', 'password', 'number',
            'boolean', 'select', 'multiselect', 'color', 'date', 'datetime',
            'file', 'image', 'markdown', 'richtext'
        ];

        $keys = [];
        foreach ($schema['sections'] as $sIndex => $section) {
            if (!is_array($section)) {
                $result->addError("Section at index {$sIndex} is not an object.");
                continue;
            }

            if (empty($section['title'])) {
                $result->addError("Section at index {$sIndex} is missing required 'title' field.");
            }

            if (!isset($section['fields']) || !is_array($section['fields'])) {
                $result->addError("Section '{$section['title']}' must contain a 'fields' array.");
                continue;
            }

            foreach ($section['fields'] as $fIndex => $field) {
                if (!is_array($field)) {
                    $result->addError("Field at index {$fIndex} in section '{$section['title']}' is not an object.");
                    continue;
                }

                $fieldLabel = $field['label'] ?? "index {$fIndex}";

                if (empty($field['key'])) {
                    $result->addError("Field '{$fieldLabel}' is missing required 'key' field.");
                } else {
                    $key = trim($field['key']);
                    if (in_array($key, $keys, true)) {
                        $result->addError("Duplicate setting key detected: '{$key}' (defined in section '{$section['title']}').");
                    }
                    $keys[] = $key;
                }

                if (empty($field['type'])) {
                    $result->addError("Field '{$fieldLabel}' is missing required 'type' field.");
                } else {
                    $type = trim($field['type']);
                    if (!in_array($type, $validTypes, true)) {
                        $result->addError("Field '{$fieldLabel}' contains unsupported type: '{$type}'. Valid types are: " . implode(', ', $validTypes));
                    }

                    // Check options for selects
                    if (in_array($type, ['select', 'multiselect'], true)) {
                        $options = $field['validation']['options'] ?? null;
                        if (!is_array($options) || empty($options)) {
                            $result->addError("Field '{$fieldLabel}' of type '{$type}' requires a non-empty options array under 'validation.options'.");
                        }
                    }

                    // Validate default value type matches field type
                    if (isset($field['default'])) {
                        $default = $field['default'];
                        if ($type === 'boolean' && !is_bool($default)) {
                            $result->addWarning("Field '{$fieldLabel}' of type 'boolean' has a default value that is not a boolean.");
                        } elseif ($type === 'number' && !is_numeric($default)) {
                            $result->addWarning("Field '{$fieldLabel}' of type 'number' has a default value that is not numeric.");
                        } elseif ($type === 'multiselect' && !is_array($default)) {
                            $result->addWarning("Field '{$fieldLabel}' of type 'multiselect' has a default value that is not an array.");
                        }
                    }
                }
            }
        }

        if ($result->passed()) {
            $result->addInfo("Settings schema matches specification with " . count($keys) . " setting fields.");
        }

        return $result;
    }
}
