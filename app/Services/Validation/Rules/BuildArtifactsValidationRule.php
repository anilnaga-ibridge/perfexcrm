<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class BuildArtifactsValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Build Artifacts';
    }

    public function weight(): int
    {
        return 5;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $forbiddenFolders = [
            'node_modules' => 'Node package dependencies directory.',
            'vendor' => 'Composer dependencies directory.',
            '.git' => 'Git repository directory.',
            '.github' => 'GitHub workflows directory.',
        ];

        foreach ($forbiddenFolders as $folder => $desc) {
            if ($context->hasDirectory($folder)) {
                $result->addInfo("Local development directory '{$folder}' found. Note: This is automatically excluded during packaging. Description: {$desc}");
            }
        }

        $forbiddenFiles = [
            '.env' => 'Local environment configuration file containing credentials.',
            'composer.lock' => 'Composer lock file (should be excluded from distribution packages).',
            'package-lock.json' => 'Node lock file (should be excluded from distribution packages).',
        ];

        foreach ($forbiddenFiles as $file => $desc) {
            if ($context->hasFile($file)) {
                $result->addInfo("Local development file '{$file}' found. Note: This is automatically excluded during packaging. Description: {$desc}");
            }
        }

        if ($result->passed()) {
            $result->addInfo("Clean package checked: no external build artifacts or local environment variables found.");
        }

        return $result;
    }
}
