<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class DocsValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Documentation';
    }

    public function weight(): int
    {
        return 5;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $expectedDocs = [
            'README.md' => 'Provides developers and administrators with installation and usage guides.',
            'LICENSE' => 'Explicitly states code terms of use and distribution permissions.',
        ];

        foreach ($expectedDocs as $file => $description) {
            if (!$context->hasFile($file)) {
                $result->addWarning("Recommended file '{$file}' is missing. Purpose: {$description}");
            } else {
                $result->addInfo("File '{$file}' exists.");
            }
        }

        if (!$context->hasFile('CHANGELOG.md')) {
            $result->addInfo("Optional file 'CHANGELOG.md' is missing.");
        }

        // Warn if translation files folder 'lang' does not exist
        if (!$context->hasDirectory('lang') && !$context->hasDirectory('resources/lang')) {
            $result->addWarning("No translations folder found (neither 'lang/' nor 'resources/lang/' exists). Shipped modules should support localization.");
        } else {
            $result->addInfo("Localization language directory is present.");
        }

        return $result;
    }
}
