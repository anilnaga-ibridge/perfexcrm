<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class VueValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Vue Pages';
    }

    public function weight(): int
    {
        return 15;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $jsPath = 'resources/js';
        if (!$context->hasDirectory($jsPath)) {
            $result->addWarning("Missing frontend source directory '{$jsPath}'.");
            return $result;
        }

        $fullJsPath = $context->getPath() . '/' . $jsPath;
        $vueFiles = $this->rglob($fullJsPath, '*.vue');

        if (empty($vueFiles)) {
            $result->addWarning("No Vue components found in '{$jsPath}'. Native modules usually declare Vue pages under pages/.");
            return $result;
        }

        $result->addInfo("Found " . count($vueFiles) . " Vue component(s).");

        $basenames = [];
        foreach ($vueFiles as $filePath) {
            $basename = strtolower(pathinfo($filePath, PATHINFO_FILENAME));
            $relativePath = str_replace($context->getPath() . '/', '', $filePath);

            if (isset($basenames[$basename])) {
                $result->addWarning("Potential component collision: '{$relativePath}' shares the same filename (case-insensitive) as '{$basenames[$basename]}'. This may cause dynamic loading conflicts.");
            }
            $basenames[$basename] = $relativePath;
        }

        if ($result->passed()) {
            $result->addInfo("Vue components successfully validated.");
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
        $filesIterator = new \RegexIterator($ite, '/\.vue$/i', \RegexIterator::MATCH);
        foreach ($filesIterator as $filePath => $object) {
            $files[] = $filePath;
        }
        return $files;
    }
}
