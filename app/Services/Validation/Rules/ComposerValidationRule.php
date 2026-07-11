<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;
use Illuminate\Support\Str;

class ComposerValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Composer PSR-4';
    }

    public function weight(): int
    {
        return 5;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        if (!$context->hasFile('composer.json')) {
            $result->addInfo("No 'composer.json' packaged by this module (will rely entirely on core runtime dynamic autoloader).");
            return $result;
        }

        $composer = $context->getJsonFile('composer.json');
        if ($composer === null) {
            $result->addError("'composer.json' exists but contains invalid JSON.");
            return $result;
        }

        $studlyAlias = Str::studly($context->getAlias());
        $expectedNamespace = "Modules\\{$studlyAlias}\\";

        $psr4 = $composer['autoload']['psr-4'] ?? null;
        if ($psr4 === null) {
            $result->addWarning("'composer.json' is missing an 'autoload.psr-4' section.");
            return $result;
        }

        $namespaceMapped = false;
        foreach ($psr4 as $namespace => $path) {
            if ($namespace === $expectedNamespace) {
                $namespaceMapped = true;
                $result->addInfo("Autoload namespace successfully mapped: '{$namespace}' -> '{$path}'");
                break;
            }
        }

        if (!$namespaceMapped) {
            $result->addWarning("None of the namespaces declared in 'composer.json' autoload section match the platform's required module namespace: '{$expectedNamespace}'.");
        }

        return $result;
    }
}
