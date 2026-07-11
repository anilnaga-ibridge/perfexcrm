<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class PermissionValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Permissions';
    }

    public function weight(): int
    {
        return 10;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        if (!$context->hasFile('permissions.json')) {
            $result->addInfo("No 'permissions.json' provided by this module.");
            return $result;
        }

        $permissions = $context->getJsonFile('permissions.json');
        if ($permissions === null) {
            $result->addError("'permissions.json' exists but contains invalid JSON.");
            return $result;
        }

        if (!is_array($permissions)) {
            $result->addError("'permissions.json' must be a JSON array.");
            return $result;
        }

        $keys = [];
        foreach ($permissions as $index => $perm) {
            if (!is_array($perm)) {
                $result->addError("Permission item at index {$index} is not an object.");
                continue;
            }

            $key = $perm['key'] ?? $perm['name'] ?? null;
            if (empty($key)) {
                $result->addError("Permission at index {$index} is missing both required 'key' and 'name' fields.");
            } else {
                $key = trim($key);
                if (in_array($key, $keys, true)) {
                    $result->addError("Duplicate permission key/name detected: '{$key}'.");
                }
                $keys[] = $key;

                // Naming convention: snake_case (e.g. view_documents, edit_documents)
                if (!preg_match('/^[a-z0-9_]+$/', $key)) {
                    $result->addWarning("Permission key/name '{$key}' should follow snake_case convention (lowercase alphanumeric and underscores only).");
                }
            }
        }

        if ($result->passed()) {
            $result->addInfo("Permissions structure is compliant with " . count($keys) . " permissions registered.");
        }

        return $result;
    }
}
