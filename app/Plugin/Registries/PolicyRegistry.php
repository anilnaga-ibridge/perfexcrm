<?php

namespace App\Plugin\Registries;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;

/**
 * Class PolicyRegistry
 * 
 * Auto-discovers and binds policy classes to models in Laravel Gates.
 */
class PolicyRegistry
{
    /**
     * Map of model classes to policy classes.
     */
    protected array $policies = [];

    /**
     * Explicitly register a model-policy pair.
     */
    public function register(string $modelClass, string $policyClass): void
    {
        $this->policies[$modelClass] = $policyClass;
        Gate::policy($modelClass, $policyClass);
    }

    /**
     * Discover policies inside a module path and register them with Gate.
     */
    public function discover(string $modulePath, string $namespacePrefix): void
    {
        $policiesPath = $modulePath . '/Policies';
        if (!File::isDirectory($policiesPath)) {
            $policiesPath = $modulePath . '/Http/Policies'; // Alternate location check
        }
        if (!File::isDirectory($policiesPath)) {
            return;
        }

        $files = File::files($policiesPath);
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $policyName = $file->getBasename('.php');
            $policyClass = rtrim($namespacePrefix, '\\') . '\\Policies\\' . $policyName;
            if (!class_exists($policyClass)) {
                $policyClass = rtrim($namespacePrefix, '\\') . '\\Http\\Policies\\' . $policyName;
            }

            if (!class_exists($policyClass)) {
                continue;
            }

            // Derive model class name by removing "Policy" suffix (e.g. HrmEmployeePolicy -> HrmEmployee)
            $modelName = preg_replace('/Policy$/', '', $policyName);
            
            // Check candidate model locations
            $candidates = [
                rtrim($namespacePrefix, '\\') . '\\Models\\' . $modelName,
                'App\\Models\\' . $modelName,
                'App\\' . $modelName,
            ];

            foreach ($candidates as $modelClass) {
                if (class_exists($modelClass)) {
                    $this->register($modelClass, $policyClass);
                    break;
                }
            }
        }
    }

    /**
     * Get all registered policies.
     */
    public function all(): array
    {
        return $this->policies;
    }
}
