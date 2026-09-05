<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PluginBridgeService
{
    /**
     * Static cache of controller instances per request.
     * Prevents duplicate instantiation and re-loading across calls.
     * Keyed by "alias:controller_name".
     */
    protected static array $controllerInstances = [];

    /**
     * Static cache of controller class name → file path mappings.
     * Avoids re-reading and regex-parsing controller files.
     */
    protected static array $classToFile = [];

    /**
     * Get or build the plugin discovery manifest (cached).
     */
    public function getManifest(string $alias): array
    {
        $cacheKey = "plugin_manifest_{$alias}_v1";

        return Cache::rememberForever($cacheKey, function () use ($alias) {
            $modulePath = base_path("Modules/{$alias}");
            if (!File::isDirectory($modulePath)) {
                $alt = str_contains($alias, '-') ? str_replace('-', '_', $alias) : str_replace('_', '-', $alias);
                $modulePath = base_path("Modules/{$alt}");
                if (!File::isDirectory($modulePath)) {
                    return [];
                }
            }

            // Read module.json metadata if exists, or parse main PHP file headers
            $manifestData = [];
            $manifestJsonPath = "{$modulePath}/module.json";
            if (File::exists($manifestJsonPath)) {
                try {
                    $manifestData = json_decode(File::get($manifestJsonPath), true) ?? [];
                } catch (\Throwable $e) {
                    Log::warning("LEGACY COMPAT: Failed to read module.json for {$alias}: " . $e->getMessage());
                }
            } else {
                $mainPhp = "{$modulePath}/{$alias}.php";
                if (File::exists($mainPhp)) {
                    $parsed = \App\Services\ModuleManager::parsePhpHeaders(File::get($mainPhp), $mainPhp);
                    if ($parsed) {
                        $manifestData = $parsed;
                    }
                } else {
                    foreach (File::glob("{$modulePath}/*.php") as $rootPhp) {
                        $bn = basename($rootPhp);
                        if (!in_array($bn, ['install.php', 'uninstall.php', 'deactivate.php', 'index.php'])) {
                            $parsed = \App\Services\ModuleManager::parsePhpHeaders(File::get($rootPhp), $rootPhp);
                            if ($parsed && !empty($parsed['name'])) {
                                $manifestData = $parsed;
                                break;
                            }
                        }
                    }
                }
            }

            // Scan controllers across controllers/, controllers/admin, controllers/api, controllers/ajax
            $controllers = [];
            $controllersDir = "{$modulePath}/controllers";
            $searchDirs = [
                $controllersDir,
                "{$controllersDir}/admin",
                "{$controllersDir}/Admin",
                "{$controllersDir}/api",
                "{$controllersDir}/Api",
                "{$controllersDir}/ajax",
                "{$controllersDir}/Ajax",
            ];
            foreach ($searchDirs as $sDir) {
                if (File::isDirectory($sDir)) {
                    foreach (File::files($sDir) as $file) {
                        if ($file->getExtension() === 'php' && $file->getFilename() !== 'index.html') {
                            $rel = ltrim(substr($file->getRealPath(), strlen($controllersDir)), '/');
                            $controllers[] = $rel;
                        }
                    }
                }
            }

            // Scan helpers
            $helpers = [];
            $helpersDir = "{$modulePath}/helpers";
            if (File::isDirectory($helpersDir)) {
                foreach (File::files($helpersDir) as $file) {
                    if ($file->getExtension() === 'php') {
                        $helpers[] = $file->getRealPath();
                    }
                }
            }

            // Scan models
            $models = [];
            $modelsDir = "{$modulePath}/models";
            if (File::isDirectory($modelsDir)) {
                foreach (File::files($modelsDir) as $file) {
                    if ($file->getExtension() === 'php') {
                        $models[] = $file->getRealPath();
                    }
                }
            }

            return [
                'alias'            => $alias,
                'name'             => $manifestData['name'] ?? ucwords(str_replace(['-', '_'], ' ', $alias)),
                'version'          => $manifestData['version'] ?? '1.0.0',
                'description'      => $manifestData['description'] ?? '',
                'author'           => $manifestData['author'] ?? '',
                'path'             => $modulePath,
                'controllers_dir'  => $controllersDir,
                'views_dir'        => "{$modulePath}/views",
                'controllers'      => array_unique($controllers),
                'helpers'          => $helpers,
                'models'           => $models,
                'settings_route'   => $manifestData['settings_route'] ?? null,
            ];
        });
    }

    /**
     * Clear manifest cache for a specific module alias.
     */
    public function clearCache(string $alias): void
    {
        Cache::forget("plugin_manifest_{$alias}_v1");
        Cache::forget("plugin_manifest_" . str_replace('-', '_', $alias) . "_v1");
        Cache::forget("plugin_manifest_" . str_replace('_', '-', $alias) . "_v1");
        self::$controllerInstances = [];
    }

    /**
     * Clear all cached controller instances and class mappings across all modules.
     */
    public static function clearAllInstances(): void
    {
        self::$controllerInstances = [];
        self::$classToFile = [];
    }

    /**
     * Bootstrap the legacy compatibility environment.
     */
    public function bootstrap(array $manifest): void
    {
        // 1. Constants & Environment setup
        if (!defined('BASEPATH')) {
            define('BASEPATH', true);
        }
        if (!defined('FCPATH')) {
            define('FCPATH', base_path('public/') . '/');
        }
        if (!defined('APPPATH')) {
            define('APPPATH', $manifest['path'] . '/');
        }
        if (!defined('VIEWPATH')) {
            define('VIEWPATH', $manifest['views_dir'] . '/');
        }

        @chdir(base_path());
        if (!str_contains(get_include_path(), base_path())) {
            set_include_path(get_include_path() . PATH_SEPARATOR . base_path());
        }

        if (!isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = url('/admin/' . ($manifest['alias'] ?? ''));
        }

        // 2. Load compatibility layer functions & classes
        require_once base_path('app/Services/CICompatLayer.php');

        // 3. Reset the CI singleton to prevent stale state between module requests
        global $CI;
        if (isset($CI)) {
            // Reset loader state for the new module
            $CI->load->resetForNewModule();
        }

        // 4. Clear compiled view cache so views are recompiled AFTER helpers are loaded
        //    (helpers define module-specific functions that the compile step needs to see)
        $cacheDir = storage_path('framework/views/plugins');
        if (is_dir($cacheDir)) {
            foreach (glob($cacheDir . '/*.php') as $cachedView) {
                @unlink($cachedView);
            }
        }

        // 5. Load module bootstrap file (CI convention: Modules/{alias}/{alias}.php)
        //    Defines constants, registers hooks, loads helpers — must run before controllers.
        $alias = $manifest['alias'] ?? '';
        $underscoreAlias = str_replace('-', '_', $alias);
        $bootstrapCandidates = [
            $manifest['path'] . "/{$underscoreAlias}.php",
            $manifest['path'] . "/{$alias}.php",
        ];
        foreach ($bootstrapCandidates as $bootstrapPath) {
            if (file_exists($bootstrapPath)) {
                try {
                    require_once $bootstrapPath;
                } catch (\Throwable $e) {
                    Log::warning("LEGACY COMPAT: Failed to load bootstrap [{$bootstrapPath}]: " . $e->getMessage());
                }
                break;
            }
        }

        // 6. Load helpers discovered in manifest
        foreach ($manifest['helpers'] as $helperPath) {
            try {
                require_once $helperPath;
            } catch (\Throwable $e) {
                Log::warning("LEGACY COMPAT: Failed to load helper [{$helperPath}]: " . $e->getMessage());
            }
        }
    }

    /**
     * Locate a controller file in a module across all standard controller directory structures.
     * Searches controllers/, controllers/admin, controllers/Admin, controllers/api, controllers/ajax, and subdirectories.
     */
    public function findControllerFile(string $modulePath, string $controllerName, array &$searchedPaths = []): ?string
    {
        $controllersDir = "{$modulePath}/controllers";
        if (!File::isDirectory($controllersDir)) {
            $searchedPaths[] = $controllersDir;
            return null;
        }

        $searchDirs = [
            $controllersDir,
            "{$controllersDir}/admin",
            "{$controllersDir}/Admin",
            "{$controllersDir}/api",
            "{$controllersDir}/Api",
            "{$controllersDir}/ajax",
            "{$controllersDir}/Ajax",
        ];

        // Also add any other immediate subdirectories under controllers/
        foreach (File::directories($controllersDir) as $subDir) {
            if (!in_array($subDir, $searchDirs)) {
                $searchDirs[] = $subDir;
            }
        }

        $nameVariants = [
            $controllerName,
            ucfirst($controllerName),
            strtolower($controllerName),
            str_replace('-', '_', $controllerName),
            ucfirst(str_replace('-', '_', $controllerName)),
            str_replace('_', '-', $controllerName),
        ];
        $nameVariants = array_unique($nameVariants);

        foreach ($searchDirs as $sDir) {
            if (File::isDirectory($sDir)) {
                foreach ($nameVariants as $variant) {
                    $candPath = "{$sDir}/{$variant}.php";
                    $searchedPaths[] = $candPath;
                    if (File::exists($candPath)) {
                        return $candPath;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Execute legacy controller action.
     * Fully idempotent: caches class mappings and controller instances per request.
     * Throws LegacyControllerResolutionException if controller, class, or method cannot be resolved.
     */
    public function executeController(array $manifest, string $controllerName, string $methodName, array $params = []): string
    {
        $alias = $manifest['alias'] ?? '';
        $cacheKey = "{$alias}:{$controllerName}";

        // 1. Return cached instance if already created this request
        if (isset(self::$controllerInstances[$cacheKey])) {
            $instance = self::$controllerInstances[$cacheKey];
            return $this->callMethod($instance, $methodName, $params, $manifest, $controllerName);
        }

        // 2. Resolve controller file path across candidate subdirectories
        $searchedPaths = [];
        $controllerFile = $this->findControllerFile($manifest['path'], $controllerName, $searchedPaths);

        if (!$controllerFile) {
            Log::debug("LEGACY COMPAT: Controller file not found: {$controllerName} in module {$alias}");
            throw new \App\Exceptions\LegacyControllerResolutionException(
                "Controller [{$controllerName}] not found in module [{$alias}].",
                $alias,
                $controllerName,
                $methodName,
                $searchedPaths,
                $manifest['controllers'] ?? []
            );
        }

        try {
            // 3. Resolve class name from file content (cached)
            if (!isset(self::$classToFile[$controllerFile])) {
                $content = File::get($controllerFile);
                if (!preg_match('/class\s+(\w+)\s+extends\s+/', $content, $m)) {
                    Log::warning("LEGACY COMPAT: No class extending another found in {$controllerFile}");
                    throw new \App\Exceptions\LegacyControllerResolutionException(
                        "No controller class extending a base class found in [{$controllerFile}].",
                        $alias,
                        $controllerName,
                        $methodName,
                        [$controllerFile],
                        $manifest['controllers'] ?? []
                    );
                }
                self::$classToFile[$controllerFile] = $m[1];
            }
            $ctrlClassName = self::$classToFile[$controllerFile];

            // 4. Load the file only if the class isn't defined yet (PHP-FPM safe)
            if (!class_exists($ctrlClassName, false)) {
                require_once $controllerFile;
            }

            if (!class_exists($ctrlClassName)) {
                Log::warning("LEGACY COMPAT: Class [{$ctrlClassName}] not found after loading {$controllerFile}");
                throw new \App\Exceptions\LegacyControllerResolutionException(
                    "Class [{$ctrlClassName}] not defined after loading [{$controllerFile}].",
                    $alias,
                    $controllerName,
                    $methodName,
                    [$controllerFile],
                    $manifest['controllers'] ?? []
                );
            }

            // 5. Create controller instance
            $instance = new $ctrlClassName();
            $ci = get_instance();

            // Sync global CI services onto the controller
            foreach (get_object_vars($ci) as $key => $value) {
                if ($value !== null) {
                    if (!property_exists($instance, $key) || $instance->$key === null) {
                        $instance->$key = $value;
                    }
                }
            }

            // Tell CILoader the active controller context
            $ci->load->setController($instance);

            // 6. Cache the instance for reuse
            self::$controllerInstances[$cacheKey] = $instance;

            // 7. Execute method
            return $this->callMethod($instance, $methodName, $params, $manifest, $controllerName);

        } catch (\App\Exceptions\LegacyControllerResolutionException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("LEGACY COMPAT: Controller execution failed [{$controllerName}::{$methodName}]: " . $e->getMessage(), [
                'file' => $controllerFile,
                'class' => $ctrlClassName ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute a method on a controller instance with output buffering.
     * Complies fully with CodeIgniter / HMVC controller dispatch.
     */
    protected function callMethod(object $instance, string $methodName, array $params, array $manifest = [], string $controllerName = ''): string
    {
        $origMethodName = $methodName;
        $methodName = str_replace('-', '_', $methodName);

        // 1. Standard CodeIgniter HMVC _remap hook (always takes precedence)
        if (method_exists($instance, '_remap')) {
            ob_start();
            try {
                $result = $instance->_remap($methodName, $params);
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
            $output = ob_get_clean();
            return is_string($result) ? $result : ($output !== false ? (string)$output : "");
        }

        $targetMethod = null;
        $callArgs = $params;

        // 2. Direct public method match
        if (method_exists($instance, $methodName) && !str_starts_with($methodName, '_')) {
            $targetMethod = $methodName;
        } elseif (method_exists($instance, 'manage_' . $methodName)) {
            $targetMethod = 'manage_' . $methodName;
        } elseif (method_exists($instance, $methodName . '_manage')) {
            $targetMethod = $methodName . '_manage';
        } elseif (in_array(strtolower($methodName), ['dashboard', 'index', 'manage', 'overview'])) {
            // 3. Fallback for root module entry points
            if (method_exists($instance, 'index')) {
                $targetMethod = 'index';
            } elseif (method_exists($instance, 'manage')) {
                $targetMethod = 'manage';
            }
        } elseif (method_exists($instance, 'index')) {
            // 4. Standard CI HMVC: segment passed as parameter to index() only if index accepts parameters
            $refMethod = new \ReflectionMethod($instance, 'index');
            if ($refMethod->getNumberOfParameters() > 0) {
                $targetMethod = 'index';
                array_unshift($callArgs, $origMethodName);
            }
        } elseif (method_exists($instance, '__call')) {
            // 5. Magic method handler
            $targetMethod = $methodName;
        } else {
            // 6. Fuzzy naming match (manage_x, x_manage)
            $ref = new \ReflectionClass($instance);
            $methods = [];
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
                if (!str_starts_with($m->getName(), '_')) {
                    $methods[] = $m->getName();
                }
            }

            $norm = strtolower($methodName);
            $singular = rtrim($norm, 's');
            $variations = [
                'manage_' . $norm,
                'manage_' . $singular,
                $norm . '_manage',
                $singular . '_manage',
            ];
            foreach ($variations as $var) {
                foreach ($methods as $m) {
                    if (strcasecmp($m, $var) === 0) {
                        $targetMethod = $m;
                        break 2;
                    }
                }
            }
        }

        if (!$targetMethod) {
            $ref = new \ReflectionClass($instance);
            $publicMethods = [];
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
                if (!str_starts_with($m->getName(), '_')) {
                    $publicMethods[] = $m->getName();
                }
            }

            throw new \App\Exceptions\LegacyControllerResolutionException(
                "Action [{$origMethodName}] could not be resolved on controller [" . get_class($instance) . "] in module [" . ($manifest['alias'] ?? '') . "].",
                $manifest['alias'] ?? '',
                $controllerName,
                $origMethodName,
                [],
                $manifest['controllers'] ?? [],
                $publicMethods
            );
        }

        ob_start();
        try {
            $result = call_user_func_array([$instance, $targetMethod], $callArgs);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $output = ob_get_clean();

        if (is_string($result)) {
            return $result;
        }
        return $output !== false ? (string)$output : "";
    }
}
