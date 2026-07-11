<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class ManifestValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Manifest';
    }

    public function weight(): int
    {
        return 20;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        if (!$context->hasFile('module.json')) {
            $result->addFatal("Missing 'module.json' manifest file in module root.");
            return $result;
        }

        $manifest = $context->getManifest();
        if (empty($manifest)) {
            $result->addFatal("'module.json' manifest is empty or is not a valid JSON document.");
            return $result;
        }

        $requiredKeys = ['name', 'alias', 'version', 'minimum_core_version'];
        foreach ($requiredKeys as $key) {
            if (!isset($manifest[$key]) || trim((string)$manifest[$key]) === '') {
                $result->addError("Manifest is missing required key: '{$key}'.");
            }
        }

        // Validate semantic version format for 'version'
        if (isset($manifest['version'])) {
            if (!preg_match('/^\d+\.\d+\.\d+$/', $manifest['version'])) {
                $result->addWarning("Manifest version '{$manifest['version']}' does not strictly follow Semantic Versioning (X.Y.Z format).");
            }
        }

        // Validate sdk_version
        if (!isset($manifest['sdk_version'])) {
            $result->addWarning("Manifest is missing 'sdk_version'. Standard native modules should declare 'sdk_version': '1.0'.");
        } elseif ($manifest['sdk_version'] !== '1.0') {
            $result->addError("Unsupported SDK version: '{$manifest['sdk_version']}'. Supported versions: '1.0'.");
        } else {
            $result->addInfo("Validated SDK version: {$manifest['sdk_version']}");
        }

        // Validate minimum_core_version
        if (isset($manifest['minimum_core_version'])) {
            if (!preg_match('/^\d+(\.\d+)*$/', $manifest['minimum_core_version'])) {
                $result->addError("Manifest 'minimum_core_version' must be a valid version number string.");
            }
        }

        // Check if alias has normalized format
        if (isset($manifest['alias'])) {
            $normalized = preg_replace('/[^a-z0-9-]/', '', strtolower($manifest['alias']));
            if ($manifest['alias'] !== $normalized) {
                $result->addError("Manifest alias '{$manifest['alias']}' is invalid. Alias must be kebab-case (lowercase, alphanumeric, and hyphens only). Expected: '{$normalized}'.");
            }
        }

        if ($result->passed()) {
            $result->addInfo("Manifest matches all specifications for module '{$manifest['name']}'.");
        }

        return $result;
    }
}
