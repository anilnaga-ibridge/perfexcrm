<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\File;

class PluginExecutionService
{
    protected $bridgeService;
    protected $viewRenderer;
    protected $contextBuilder;

    public function __construct(
        PluginBridgeService $bridgeService,
        LegacyViewRenderer $viewRenderer,
        LegacyViewContextBuilder $contextBuilder
    ) {
        $this->bridgeService = $bridgeService;
        $this->viewRenderer = $viewRenderer;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * Resolve and execute a legacy view template or controller page.
     */
    public function executePage(string $alias, ?string $page, array $manifest): ?string
    {
        $page = $page ?? 'index';
        $segments = explode('/', $page);
        $controllerName = null;
        $methodName = 'index';
        $params = [];

        $controllersDir = $manifest['controllers_dir'];
        if (count($segments) > 0 && !empty($segments[0])) {
            $ctrlSegment = $segments[0];
            $foundSubCtrl = $this->bridgeService->findControllerFile($manifest['path'], $ctrlSegment);
            if ($foundSubCtrl) {
                $controllerName = pathinfo($foundSubCtrl, PATHINFO_FILENAME);
                $methodName = isset($segments[1]) ? str_replace('-', '_', $segments[1]) : 'index';
                $params = array_slice($segments, 2);
            } else {
                $methodName = str_replace('-', '_', $segments[0]);
                $params = array_slice($segments, 1);
            }
        }

        if (!$controllerName && is_dir($controllersDir)) {
            $aliasClass = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $alias)));
            $candidates = [$alias, $aliasClass, str_replace('-', '_', $alias)];
            foreach ($candidates as $cand) {
                $foundCtrl = $this->bridgeService->findControllerFile($manifest['path'], $cand);
                if ($foundCtrl) {
                    $controllerName = pathinfo($foundCtrl, PATHINFO_FILENAME);
                    break;
                }
            }
            if (!$controllerName) {
                $files = File::glob("{$controllersDir}/*.php");
                foreach ($files as $f) {
                    if (basename($f) !== 'index.html') {
                        $controllerName = pathinfo($f, PATHINFO_FILENAME);
                        break;
                    }
                }
            }
        }

        if ($controllerName) {
            try {
                $htmlContent = $this->bridgeService->executeController($manifest, $controllerName, $methodName, $params);
                if ($htmlContent !== null && $htmlContent !== '') {
                    return $htmlContent;
                }
            } catch (\App\Exceptions\LegacyControllerResolutionException $e) {
                // Try entry point fallbacks before falling back to views
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::debug("Direct controller execution failed for [{$controllerName}::{$methodName}], trying fallbacks: " . $e->getMessage());
            }

            // Fallback for default module entry points (e.g. /dashboard or /{alias} or empty)
            if (in_array(strtolower($methodName), ['dashboard', 'index', 'manage', 'overview']) || $methodName === $alias) {
                foreach (['index', 'manage'] as $fallbackMethod) {
                    if ($fallbackMethod !== $methodName) {
                        try {
                            $htmlContent = $this->bridgeService->executeController($manifest, $controllerName, $fallbackMethod, $params);
                            if ($htmlContent !== null && $htmlContent !== '') {
                                return $htmlContent;
                            }
                        } catch (\Throwable $e) {}
                    }
                }
            }

            $htmlContent = $this->tryResolveAndExecute($manifest, $controllerName, $methodName, $page, $params);
            if ($htmlContent !== null && $htmlContent !== '') {
                return $htmlContent;
            }
        }

        $viewsDir = $manifest['views_dir'];
        $pageUnderscored = str_replace('-', '_', $page);
        $possiblePaths = [
            "{$viewsDir}/{$page}.php",
            "{$viewsDir}/{$pageUnderscored}.php",
            "{$viewsDir}/{$alias}/{$page}.php",
            "{$viewsDir}/{$alias}/{$pageUnderscored}.php",
            "{$viewsDir}/{$page}/index.php",
            "{$viewsDir}/{$pageUnderscored}/index.php",
            "{$viewsDir}/{$alias}/manage.php",
            "{$viewsDir}/{$alias}/index.php",
            "{$viewsDir}/manage.php",
            "{$viewsDir}/index.php",
            "{$viewsDir}/{$page}/manage.php",
            "{$viewsDir}/{$pageUnderscored}/manage.php",
            "{$viewsDir}/{$page}/{$page}_manage.php",
            "{$viewsDir}/{$pageUnderscored}/{$pageUnderscored}_manage.php",
        ];

        if (str_ends_with($page, 's')) {
            $singular = rtrim($page, 's');
            $possiblePaths[] = "{$viewsDir}/{$page}/{$singular}_manage.php";
            $possiblePaths[] = "{$viewsDir}/{$pageUnderscored}/" . rtrim($pageUnderscored, 's') . "_manage.php";
        }

        if ($methodName !== $page) {
            $possiblePaths[] = "{$viewsDir}/{$methodName}.php";
            $possiblePaths[] = "{$viewsDir}/{$methodName}/index.php";
            $possiblePaths[] = "{$viewsDir}/{$methodName}/manage.php";
            $possiblePaths[] = "{$viewsDir}/{$methodName}/{$methodName}_manage.php";
            if (str_ends_with($methodName, 's')) {
                $singularM = rtrim($methodName, 's');
                $possiblePaths[] = "{$viewsDir}/{$methodName}/{$singularM}_manage.php";
            }
        }

        $viewFilePath = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $viewFilePath = $path;
                break;
            }
        }

        if (!$viewFilePath && File::isDirectory("{$viewsDir}/{$page}")) {
            $files = File::glob("{$viewsDir}/{$page}/*.php");
            $partialPrefixes = ['export_', 'table_', 'modal_', 'tab_', 'staff_payslip_', 'add_', 'import_'];
            $managePatterns = ['manage', 'list', 'index'];
            if (count($files) === 1) {
                $viewFilePath = $files[0];
            } else {
                foreach ($files as $file) {
                    $fn = strtolower(basename($file));
                    $isPartial = false;
                    foreach ($partialPrefixes as $prefix) {
                        if (str_starts_with($fn, $prefix)) {
                            $isPartial = true;
                            break;
                        }
                    }
                    if ($isPartial) continue;
                    if (in_array($fn, ['activate.php', 'index.php'])) continue;
                    foreach ($managePatterns as $pattern) {
                        if (str_contains($fn, $pattern)) {
                            $viewFilePath = $file;
                            break 2;
                        }
                    }
                }
                if (!$viewFilePath && !empty($files)) {
                    $candidates = array_filter($files, function ($f) use ($partialPrefixes) {
                        $fn = strtolower(basename($f));
                        foreach ($partialPrefixes as $prefix) {
                            if (str_starts_with($fn, $prefix)) return false;
                        }
                        return true;
                    });
                    if (!empty($candidates)) {
                        $viewFilePath = reset($candidates);
                    }
                }
            }
        }

        if (!$viewFilePath) {
            throw new \App\Exceptions\LegacyControllerResolutionException(
                "Could not resolve an active controller method or view template for [{$page}] in module [{$alias}].",
                $alias,
                $controllerName ?? '',
                $methodName ?? '',
                $possiblePaths,
                $manifest['controllers'] ?? []
            );
        }

        $module = new Module([
            'name' => $manifest['name'] ?? ucwords(str_replace(['-', '_'], ' ', $alias)),
            'alias' => $alias,
            'version' => $manifest['version'] ?? '1.0.0',
            'status' => 'active',
        ]);
        try {
            $dbMod = Module::where('alias', $alias)->first();
            if ($dbMod) {
                $module = $dbMod;
            }
        } catch (\Throwable $e) {}

        $variables = $this->contextBuilder->build($page, $module ? $module->toArray() : []);
        return $this->viewRenderer->render($viewFilePath, $variables);
    }

    /**
     * When direct method execution fails, scan the controller file for public methods
     * and try common name patterns to find the correct one.
     */
    protected function tryResolveAndExecute(array $manifest, string $controllerName, string $methodName, string $page, array $params): ?string
    {
        $controllersDir = $manifest['controllers_dir'];
        $controllerFile = null;

        $candidates = [ucfirst($controllerName), strtolower($controllerName)];
        foreach ($candidates as $cand) {
            $path = "{$controllersDir}/{$cand}.php";
            if (File::exists($path)) {
                $controllerFile = $path;
                break;
            }
        }

        if (!$controllerFile) {
            return null;
        }

        $content = File::get($controllerFile);
        if (!preg_match('/class\s+(\w+)\s+extends\s+/', $content, $classMatch)) {
            return null;
        }
        $className = $classMatch[1];

        if (!class_exists($className, false)) {
            require_once $controllerFile;
        }
        if (!class_exists($className)) {
            return null;
        }

        $ref = new \ReflectionClass($className);
        $publicMethods = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($name === '__construct' || $name === '__get' || str_starts_with($name, '_')) {
                continue;
            }
            $publicMethods[] = $name;
        }

        $pageNorm = strtolower(str_replace('-', '_', $page));
        $pageSingular = rtrim($pageNorm, 's');

        $candidates = [
            $methodName,
            'manage_' . $pageSingular,
            'manage_' . $pageNorm,
            $pageNorm,
            $pageSingular,
            $pageNorm . '_manage',
            $pageSingular . '_manage',
            'manage_' . $pageNorm . 's',
            $pageNorm . '_table',
        ];

        $resolved = null;
        $methodSet = array_flip($publicMethods);
        foreach ($candidates as $cand) {
            if (isset($methodSet[$cand])) {
                $resolved = $cand;
                break;
            }
        }

        if (!$resolved) {
            foreach ($publicMethods as $m) {
                $mNorm = strtolower(str_replace('-', '_', $m));
                $mSingular = rtrim($mNorm, 's');
                if ($mNorm === $pageNorm || $mNorm === $pageSingular || $mSingular === $pageNorm || $mSingular === $pageSingular) {
                    $resolved = $m;
                    break;
                }
                if (str_contains($mNorm, $pageNorm) || str_contains($pageNorm, $mNorm)) {
                    $resolved = $m;
                    break;
                }
            }
        }

        if (!$resolved) {
            return null;
        }

        $ci = get_instance();
        $instance = new $className();
        foreach (get_object_vars($ci) as $key => $value) {
            if ($value !== null) {
                if (!property_exists($instance, $key) || $instance->$key === null) {
                    $instance->$key = $value;
                }
            }
        }
        $ci->load->setController($instance);

        ob_start();
        try {
            $result = call_user_func_array([$instance, $resolved], $params);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        $output = ob_get_clean();

        if (is_string($result)) {
            return $result;
        }
        return $output ?: null;
    }
}
