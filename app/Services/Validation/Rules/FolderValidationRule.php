<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class FolderValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Folders';
    }

    public function weight(): int
    {
        return 10;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $expectedFolders = [
            'routes' => 'Contains module-specific routing definitions.',
            'Http/Controllers' => 'Contains module API and page controllers.',
            'Models' => 'Contains Eloquent models.',
            'Database/Migrations' => 'Contains database schema definition files.',
            'resources/js' => 'Contains frontend Vue and JS source files.',
        ];

        foreach ($expectedFolders as $folder => $description) {
            if (!$context->hasDirectory($folder)) {
                $result->addWarning("Recommended directory '{$folder}' is missing. Purpose: {$description}");
            } else {
                $result->addInfo("Directory '{$folder}' exists.");
            }
        }

        if ($result->passed()) {
            $result->addInfo("Core folder structure is valid.");
        }

        return $result;
    }
}
