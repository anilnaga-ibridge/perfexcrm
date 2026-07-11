<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ControllerValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Controllers';
    }

    public function weight(): int
    {
        return 15;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $controllerDir = $context->getPath() . '/Http/Controllers';
        $legacyControllerDir = $context->getPath() . '/Controllers';

        $dirToScan = null;
        if (is_dir($controllerDir)) {
            $dirToScan = $controllerDir;
        } elseif (is_dir($legacyControllerDir)) {
            $dirToScan = $legacyControllerDir;
        }

        if ($dirToScan === null) {
            $result->addInfo("No controllers folder found to validate.");
            return $result;
        }

        $studlyAlias = Str::studly($context->getAlias());
        $expectedNsPrefix = "Modules\\" . $studlyAlias;

        $files = $this->rglob($dirToScan, '*.php');
        if (empty($files)) {
            $result->addInfo("No controller PHP files found.");
            return $result;
        }

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            $fileName = basename($filePath);

            // Extract namespace
            if (preg_match('/namespace\s+([^;]+);/i', $content, $nsMatches)) {
                $namespace = trim($nsMatches[1]);
                if (!str_starts_with($namespace, $expectedNsPrefix)) {
                    $result->addError("Controller '{$fileName}' has invalid namespace '{$namespace}'. Expected it to start with '{$expectedNsPrefix}'.");
                } else {
                    $result->addInfo("Validated namespace for '{$fileName}': {$namespace}");
                }
            } else {
                $result->addError("Controller '{$fileName}' is missing a namespace declaration.");
            }

            // Extract class name
            if (preg_match('/class\s+([A-Za-z0-9_]+)/i', $content, $classMatches)) {
                $className = $classMatches[1];
                $expectedClassName = pathinfo($filePath, PATHINFO_FILENAME);
                if ($className !== $expectedClassName) {
                    $result->addError("Class name '{$className}' inside '{$fileName}' does not match the filename.");
                }
            }
        }

        if ($result->passed()) {
            $result->addInfo("All controllers matched namespace prefix '{$expectedNsPrefix}'.");
        }

        return $result;
    }

    /**
     * Recursively find files matching pattern.
     */
    private function rglob(string $pattern, string $matchPattern): array
    {
        $files = [];
        $dir = new \RecursiveDirectoryIterator($pattern);
        $ite = new \RecursiveIteratorIterator($dir);
        $filesIterator = new \RegexIterator($ite, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($filesIterator as $file) {
            $files[] = $file[0];
        }
        return $files;
    }
}
