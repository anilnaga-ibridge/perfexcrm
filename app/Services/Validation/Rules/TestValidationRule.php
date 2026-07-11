<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class TestValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Test Coverage';
    }

    public function weight(): int
    {
        return 5;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $hasTests = false;
        $testDirs = ['tests', 'Tests'];

        foreach ($testDirs as $dir) {
            if ($context->hasDirectory($dir)) {
                $hasTests = true;
                break;
            }
        }

        if (!$hasTests) {
            $result->addWarning("No tests directory found. It is highly recommended to package unit/feature tests with the module.");
            return $result;
        }

        // Search for test files recursively
        $testFiles = $this->rglob($context->getPath() . '/tests', '*Test.php');
        if (empty($testFiles)) {
            $testFiles = $this->rglob($context->getPath() . '/Tests', '*Test.php');
        }

        if (empty($testFiles)) {
            $result->addWarning("Tests directory exists, but no PHPUnit test classes (*Test.php) were found.");
        } else {
            $result->addInfo("Found " . count($testFiles) . " test file(s) in tests/.");
        }

        return $result;
    }

    /**
     * Recursively find files matching extension.
     */
    private function rglob(string $pattern, string $matchPattern): array
    {
        $files = [];
        if (!is_dir($pattern)) {
            return $files;
        }
        $dir = new \RecursiveDirectoryIterator($pattern);
        $ite = new \RecursiveIteratorIterator($dir);
        $filesIterator = new \RegexIterator($ite, '/Test\.php$/i', \RegexIterator::MATCH);
        foreach ($filesIterator as $filePath => $object) {
            $files[] = $filePath;
        }
        return $files;
    }
}
