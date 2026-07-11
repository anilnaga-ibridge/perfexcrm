<?php

namespace App\Services\Validation\Rules;

use App\Services\Validation\ModuleValidationRule;
use App\Services\Validation\ModuleContext;
use App\Services\Validation\ValidationResult;

class RouteValidationRule implements ModuleValidationRule
{
    public function name(): string
    {
        return 'Routes';
    }

    public function weight(): int
    {
        return 15;
    }

    public function validate(ModuleContext $context): ValidationResult
    {
        $result = new ValidationResult($this->name());

        $hasApi = $context->hasFile('routes/api.php');
        $hasWeb = $context->hasFile('routes/web.php');

        if (!$hasApi && !$hasWeb) {
            $result->addWarning("No route files found (neither 'routes/api.php' nor 'routes/web.php' exists).");
            return $result;
        }

        $files = [];
        if ($hasApi) $files[] = 'routes/api.php';
        if ($hasWeb) $files[] = 'routes/web.php';

        $totalRoutes = 0;
        foreach ($files as $file) {
            $content = $context->getFileContent($file);
            if (empty($content)) {
                $result->addWarning("Route file '{$file}' is empty.");
                continue;
            }

            // Find controllers referenced: look for class reference [ControllerName]::class or string 'ControllerName@method'
            // Match pattern like: [A-Za-z0-9_]+Controller
            preg_match_all('/([A-Za-z0-9_]+Controller)/', $content, $matches);
            $controllers = array_unique($matches[1] ?? []);

            foreach ($controllers as $ctrl) {
                // If it is a generic Controller, ignore
                if ($ctrl === 'Controller') {
                    continue;
                }

                // Check if the controller file exists in Http/Controllers/ (or direct subfolders)
                $found = false;
                $searchPaths = [
                    "Http/Controllers/{$ctrl}.php",
                    "Http/Controllers/Api/{$ctrl}.php",
                    "Controllers/{$ctrl}.php",
                    "Controllers/Api/{$ctrl}.php",
                ];

                foreach ($searchPaths as $path) {
                    if ($context->hasFile($path)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $result->addError("Route file '{$file}' references controller '{$ctrl}' which could not be found in the module's Http/Controllers directory.");
                } else {
                    $result->addInfo("Resolved controller '{$ctrl}' referenced in '{$file}'.");
                }
            }

            // Parse routes to check for actual duplicates (method + path)
            preg_match_all('/Route::(get|post|put|delete|patch|options|any)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $pathMatches);
            $methods = $pathMatches[1] ?? [];
            $paths = $pathMatches[2] ?? [];
            $totalRoutes += count($paths);

            $routeKeys = [];
            for ($i = 0; $i < count($paths); $i++) {
                $routeKeys[] = strtoupper($methods[$i]) . ' ' . $paths[$i];
            }

            $routeCounts = array_count_values($routeKeys);
            foreach ($routeCounts as $routeKey => $count) {
                if ($count > 1) {
                    $result->addWarning("Route '{$routeKey}' is defined {$count} times in '{$file}'. Ensure this is not a duplicate definition.");
                }
            }
        }

        if ($result->passed()) {
            $result->addInfo("Validated route files with {$totalRoutes} registered routes.");
        }

        return $result;
    }
}
