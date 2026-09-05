<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\LegacyControllerResolutionException;
use App\Models\Module;
use App\Services\PluginBridgeService;
use App\Services\PluginExecutionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PluginBridgeController extends Controller
{
    protected $bridgeService;
    protected $executionService;

    public function __construct(
        PluginBridgeService $bridgeService,
        PluginExecutionService $executionService
    ) {
        $this->bridgeService = $bridgeService;
        $this->executionService = $executionService;
    }

    /**
     * Authenticate request explicitly without hidden side-effects.
     */
    protected function authenticateRequest(Request $request): void
    {
        if (auth('web')->check() || auth('sanctum')->check()) {
            return;
        }

        // Dedicated development authentication (only if explicitly enabled in local environment)
        if (app()->environment('local') && config('app.legacy_dev_auth', false)) {
            $adminUser = \App\Models\User::first();
            if ($adminUser) {
                auth('web')->login($adminUser);
                return;
            }
        }

        abort(403, 'Unauthenticated');
    }

    /**
     * Resolve module directory on disk (filesystem is the authoritative source of truth).
     * Preserves original alias without eager underscore/hyphen conversion.
     */
    protected function resolveModulePath(string $alias): ?string
    {
        // 1. Exact folder match (authoritative)
        $exactPath = base_path("Modules/{$alias}");
        if (File::isDirectory($exactPath)) {
            return $exactPath;
        }

        // 2. Case-insensitive or alternate hyphen/underscore check on disk
        $modulesDir = base_path('Modules');
        if (!File::isDirectory($modulesDir)) {
            return null;
        }

        $candidates = [
            str_replace('-', '_', $alias),
            str_replace('_', '-', $alias),
            strtolower($alias),
        ];

        foreach (File::directories($modulesDir) as $dir) {
            $basename = basename($dir);
            if (strcasecmp($basename, $alias) === 0 || in_array($basename, $candidates)) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Render a legacy CodeIgniter plugin bridge page.
     * Filesystem is primary source of truth; database serves as metadata cache.
     */
    public function renderPage(Request $request, string $alias, ?string $page = null)
    {
        $this->authenticateRequest($request);

        // 1. Filesystem-first module resolution
        $modulePath = $this->resolveModulePath($alias);
        if (!$modulePath) {
            abort(404, "Module directory [{$alias}] not found on filesystem.");
        }

        // Canonical alias is the authoritative folder name on disk
        $canonicalAlias = basename($modulePath);
        $manifest = $this->bridgeService->getManifest($canonicalAlias);

        // Build or fetch Module model (cached/fallback)
        $module = new Module([
            'name'        => $manifest['name'] ?? ucwords(str_replace(['-', '_'], ' ', $canonicalAlias)),
            'alias'       => $canonicalAlias,
            'version'     => $manifest['version'] ?? '1.0.0',
            'description' => $manifest['description'] ?? '',
            'status'      => 'active',
        ]);
        try {
            $dbMod = Module::where('alias', $canonicalAlias)->first();
            if ($dbMod) {
                $module = $dbMod;
            }
        } catch (\Throwable $e) {}

        // 2. Bootstrap legacy environment
        $this->bridgeService->bootstrap($manifest);

        // 3. Delegate execution to PluginExecutionService with descriptive error handling
        try {
            $htmlContent = $this->executionService->executePage($canonicalAlias, $page, $manifest);
            return response($htmlContent)->header('Content-Type', 'text/html; charset=utf-8');
        } catch (LegacyControllerResolutionException $e) {
            if (config('app.debug')) {
                return response($this->renderDeveloperDiagnostics($module, $canonicalAlias, $page, $manifest, $e), 404)
                    ->header('Content-Type', 'text/html; charset=utf-8');
            }
            return response($this->renderFriendlyErrorPage($module, $canonicalAlias, $page), 404)
                ->header('Content-Type', 'text/html; charset=utf-8');
        } catch (\Throwable $e) {
            Log::error("Legacy page execution failed for [{$canonicalAlias}/{$page}]: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute a legacy dynamic admin controller and return the full HTML page or JSON.
     * Implements full CodeIgniter HMVC: Module -> Subcontroller / Main Controller -> Action -> Params.
     */
    public function executeApi(Request $request, string $controller, ?string $method = null, ?string $params = null)
    {
        $this->authenticateRequest($request);

        $methodName = $method !== null ? $method : 'index';
        $paramArgs = $params !== null && $params !== '' ? explode('/', $params) : [];

        // 1. Filesystem-first discovery: Locate which module owns this controller
        $targetModulePath = $this->resolveModulePath($controller);
        $targetAlias = $targetModulePath ? basename($targetModulePath) : null;
        $controllerFile = null;

        if ($targetAlias) {
            $manifest = $this->bridgeService->getManifest($targetAlias);
            $this->bridgeService->bootstrap($manifest);

            // Pattern A: Sub-controller file exists (e.g. controllers/{Method}.php or controllers/admin/{Method}.php)
            if ($method !== null) {
                $subFile = $this->bridgeService->findControllerFile($manifest['path'], $method);
                if ($subFile) {
                    $subCtrlName = pathinfo($subFile, PATHINFO_FILENAME);
                    $subMethod = !empty($paramArgs) ? array_shift($paramArgs) : 'index';
                    try {
                        $htmlContent = $this->bridgeService->executeController($manifest, $subCtrlName, $subMethod, $paramArgs);
                        return $this->formatResponse($request, $htmlContent);
                    } catch (LegacyControllerResolutionException $e) {
                        return $this->handleResolutionFailure($request, $e, $manifest);
                    }
                }
            }

            // Pattern B: Main module controller (e.g. controllers/{Module}.php)
            $mainFile = $this->bridgeService->findControllerFile($manifest['path'], $targetAlias);
            if (!$mainFile) {
                $mainFile = $this->bridgeService->findControllerFile($manifest['path'], $controller);
            }
            if ($mainFile) {
                $mainCtrlName = pathinfo($mainFile, PATHINFO_FILENAME);
                try {
                    $htmlContent = $this->bridgeService->executeController($manifest, $mainCtrlName, $methodName, $paramArgs);
                    return $this->formatResponse($request, $htmlContent);
                } catch (LegacyControllerResolutionException $e) {
                    return $this->handleResolutionFailure($request, $e, $manifest);
                }
            }
        }

        // 2. Search all module directories on disk for a controller file matching $controller
        $modulesDir = base_path('Modules');
        if (File::isDirectory($modulesDir)) {
            foreach (File::directories($modulesDir) as $mDir) {
                $alias = basename($mDir);
                $foundFile = $this->bridgeService->findControllerFile($mDir, $controller);
                if ($foundFile) {
                    $manifest = $this->bridgeService->getManifest($alias);
                    $this->bridgeService->bootstrap($manifest);
                    $ctrlName = pathinfo($foundFile, PATHINFO_FILENAME);
                    try {
                        $htmlContent = $this->bridgeService->executeController($manifest, $ctrlName, $methodName, $paramArgs);
                        return $this->formatResponse($request, $htmlContent);
                    } catch (LegacyControllerResolutionException $e) {
                        return $this->handleResolutionFailure($request, $e, $manifest);
                    }
                }
            }
        }

        // 3. Controller not found on disk
        $searched = [];
        if (File::isDirectory($modulesDir)) {
            foreach (File::directories($modulesDir) as $mDir) {
                $searched[] = "{$mDir}/controllers/{$controller}.php";
            }
        }

        $ex = new LegacyControllerResolutionException(
            "Legacy controller [{$controller}] not found in any module.",
            $targetAlias ?? '',
            $controller,
            $methodName,
            $searched
        );

        return $this->handleResolutionFailure($request, $ex, $manifest ?? []);
    }

    /**
     * Handle resolution failure appropriately for JSON/AJAX and web requests.
     */
    protected function handleResolutionFailure(Request $request, LegacyControllerResolutionException $e, array $manifest)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error'                  => $e->getMessage(),
                'module'                 => $e->getModuleAlias(),
                'controller'             => $e->getControllerName(),
                'action'                 => $e->getMethodName(),
                'searched_paths'         => $e->getSearchedPaths(),
                'discovered_controllers' => $e->getDiscoveredControllers(),
                'discovered_methods'     => $e->getDiscoveredMethods(),
            ], 404);
        }

        if (config('app.debug')) {
            $module = new Module([
                'name'  => $manifest['name'] ?? ucwords(str_replace(['-', '_'], ' ', $e->getModuleAlias() ?: $e->getControllerName())),
                'alias' => $e->getModuleAlias() ?: $e->getControllerName(),
            ]);
            return response($this->renderDeveloperDiagnostics($module, $e->getModuleAlias(), $e->getMethodName(), $manifest, $e), 404)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        abort(404, "Page not found.");
    }

    /**
     * Format response with robust JSON detection.
     * Strips UTF-8 BOM, trims whitespace, verifies json_decode + json_last_error,
     * and safely handles leading PHP notices/warnings before JSON.
     */
    protected function formatResponse(Request $request, string $content)
    {
        // 1. Strip UTF-8 Byte Order Mark (BOM) and whitespace
        $clean = preg_replace('/^\xEF\xBB\xBF/', '', trim($content));

        // 2. Direct JSON decode validation
        $isDirectJson = false;
        if ($clean !== '' && ($clean[0] === '{' || $clean[0] === '[')) {
            $decoded = json_decode($clean);
            if (json_last_error() === JSON_ERROR_NONE && (is_object($decoded) || is_array($decoded))) {
                $isDirectJson = true;
            }
        }

        if ($isDirectJson) {
            return response($clean)->header('Content-Type', 'application/json; charset=utf-8');
        }

        // 3. Substring JSON detection for outputs with leading notices or whitespace
        $firstBrace = strpos($clean, '{');
        $firstBracket = strpos($clean, '[');
        $startPos = false;
        if ($firstBrace !== false && $firstBracket !== false) {
            $startPos = min($firstBrace, $firstBracket);
        } elseif ($firstBrace !== false) {
            $startPos = $firstBrace;
        } elseif ($firstBracket !== false) {
            $startPos = $firstBracket;
        }

        if ($startPos !== false) {
            $candidate = substr($clean, $startPos);
            $lastBrace = strrpos($candidate, '}');
            $lastBracket = strrpos($candidate, ']');
            $endPos = false;
            if ($lastBrace !== false && $lastBracket !== false) {
                $endPos = max($lastBrace, $lastBracket);
            } elseif ($lastBrace !== false) {
                $endPos = $lastBrace;
            } elseif ($lastBracket !== false) {
                $endPos = $lastBracket;
            }

            if ($endPos !== false) {
                $jsonPayload = substr($candidate, 0, $endPos + 1);
                $decoded = json_decode($jsonPayload);
                if (json_last_error() === JSON_ERROR_NONE && (is_object($decoded) || is_array($decoded))) {
                    if ($request->ajax() || $request->wantsJson() || $startPos > 0) {
                        return response($jsonPayload)->header('Content-Type', 'application/json; charset=utf-8');
                    }
                }
            }
        }

        $contentType = ($request->ajax() || $request->wantsJson())
            ? 'application/json; charset=utf-8'
            : 'text/html; charset=utf-8';

        return response($clean)->header('Content-Type', $contentType);
    }

    /**
     * Render developer-friendly diagnostics when a view or controller method is missing.
     */
    protected function renderDeveloperDiagnostics(
        Module $module,
        string $alias,
        ?string $page,
        array $manifest,
        ?LegacyControllerResolutionException $exception = null
    ): string {
        ob_start();
        $pageTitle = $module->name . ' - Resolution Diagnostics';
        init_head($pageTitle);

        $moduleName = htmlspecialchars($module->name);
        $reqPage = htmlspecialchars($page ?? 'index');
        $modulePath = htmlspecialchars($manifest['path'] ?? base_path("Modules/{$alias}"));
        $searchedPaths = $exception ? $exception->getSearchedPaths() : [
            ($manifest['views_dir'] ?? '') . "/{$page}.php",
            ($manifest['views_dir'] ?? '') . "/manage_{$page}.php",
            ($manifest['controllers_dir'] ?? '') . "/*::{$page}()",
        ];
        $controllers = !empty($exception?->getDiscoveredControllers()) ? $exception->getDiscoveredControllers() : ($manifest['controllers'] ?? []);
        $discoveredMethods = $exception ? $exception->getDiscoveredMethods() : [];

        echo '<div class="developer-diagnostics" style="max-width: 100%; font-family: \'Public Sans\', sans-serif;">';

        // Warning Alert Header
        echo '  <div class="panel" style="border-left: 4px solid #EA5455; padding: 20px; margin-bottom: 20px;">';
        echo '    <div style="display: flex; align-items: flex-start; gap: 14px;">';
        echo '      <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(234, 84, 85, 0.12); display: flex; align-items: center; justify-content: center; color: #EA5455; font-size: 20px; flex-shrink: 0;"><i class="fa fa-exclamation-triangle"></i></div>';
        echo '      <div style="flex: 1;">';
        echo '        <h3 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #1E293B;">Module View or Controller Action Not Found</h3>';
        echo '        <p style="margin: 0; font-size: 13.5px; color: #64748B;">' . htmlspecialchars($exception?->getMessage() ?? "The route /admin/module/{$alias}/{$reqPage} could not resolve an active controller method or view in {$moduleName}.") . '</p>';
        echo '      </div>';
        echo '    </div>';
        echo '  </div>';

        // Diagnostic Breakdown Cards
        echo '  <div class="row">';
        echo '    <div class="col-md-7">';
        echo '      <div class="panel panel-default">';
        echo '        <div class="panel-heading"><i class="fa fa-search" style="color: #7367F0; margin-right: 6px;"></i> Checked Locations</div>';
        echo '        <div class="panel-body" style="font-size: 13px;">';
        echo '          <p style="margin-bottom: 8px; color: #475569;">The bridge inspected the following candidate paths:</p>';
        echo '          <ul style="list-style: none; padding-left: 0; margin-bottom: 16px;">';
        foreach (array_slice($searchedPaths, 0, 10) as $sp) {
            echo '            <li style="padding: 6px 10px; background: #F8F7FA; border-radius: 6px; margin-bottom: 6px; font-family: monospace; font-size: 12px; color: #EA5455;"><i class="fa fa-times-circle" style="margin-right: 6px;"></i> ' . htmlspecialchars($sp) . '</li>';
        }
        if (count($searchedPaths) > 10) {
            echo '            <li style="color: #94A3B8; font-size: 11px; padding: 4px 10px;">... and ' . (count($searchedPaths) - 10) . ' more candidate paths</li>';
        }
        echo '          </ul>';
        echo '          <div style="display: flex; gap: 8px; margin-top: 14px;">';
        echo '            <a href="' . admin_url('setup/modules') . '" target="_top" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Modules Setup</a>';
        echo '          </div>';
        echo '        </div>';
        echo '      </div>';
        echo '    </div>';

        echo '    <div class="col-md-5">';
        echo '      <div class="panel panel-default">';
        echo '        <div class="panel-heading"><i class="fa fa-folder-open" style="color: #0284C7; margin-right: 6px;"></i> Discovered Module Architecture</div>';
        echo '        <div class="panel-body" style="font-size: 12.5px;">';
        echo '          <div style="margin-bottom: 10px;"><strong>Module Path:</strong> <code style="font-size: 11px;">' . $modulePath . '</code></div>';
        echo '          <div style="margin-bottom: 6px;"><strong>Controllers Discovered (' . count($controllers) . '):</strong></div>';
        echo '          <ul style="padding-left: 18px; margin-bottom: 12px; color: #475569;">';
        if (!empty($controllers)) {
            foreach ($controllers as $c) { echo '<li><code>' . htmlspecialchars($c) . '</code></li>'; }
        } else {
            echo '<li class="text-muted">No PHP controllers found.</li>';
        }
        echo '          </ul>';
        if (!empty($discoveredMethods)) {
            echo '          <div style="margin-bottom: 6px;"><strong>Available Controller Methods:</strong></div>';
            echo '          <div style="display: flex; flex-wrap: wrap; gap: 4px;">';
            foreach ($discoveredMethods as $dm) {
                echo '<span class="label label-default" style="font-family: monospace;">' . htmlspecialchars($dm) . '()</span>';
            }
            echo '          </div>';
        }
        echo '        </div>';
        echo '      </div>';
        echo '    </div>';
        echo '  </div>';

        echo '</div>';

        init_tail();
        return ob_get_clean();
    }

    /**
     * Render a clean, friendly error page when a module page cannot be resolved in production.
     */
    protected function renderFriendlyErrorPage(Module $module, string $alias, ?string $page): string
    {
        ob_start();
        $pageTitle = $module->name . ' - Page Not Available';
        init_head($pageTitle);

        $moduleName = htmlspecialchars($module->name);
        $pageLabel = htmlspecialchars($page ? ucwords(str_replace(['-', '_'], ' ', $page)) : 'Module Page');

        echo '<div class="module-error-container" style="max-width: 800px; margin: 40px auto; font-family: \'Public Sans\', sans-serif;">';
        echo '  <div class="panel" style="border-left: 4px solid #7367F0; padding: 30px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); border-radius: 8px;">';
        echo '    <div style="text-align: center; margin-bottom: 20px;">';
        echo '      <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(115, 103, 240, 0.12); display: inline-flex; align-items: center; justify-content: center; color: #7367F0; font-size: 26px; margin-bottom: 12px;"><i class="fa fa-cubes"></i></div>';
        echo '      <h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1E293B;">' . $moduleName . '</h3>';
        echo '      <p style="margin: 0; font-size: 14px; color: #64748B;">The requested section <strong>' . $pageLabel . '</strong> could not be loaded.</p>';
        echo '    </div>';
        echo '    <div style="background: #F8F7FA; border-radius: 6px; padding: 14px 18px; margin-bottom: 20px; font-size: 13px; color: #475569;">';
        echo '      <p style="margin: 0 0 6px 0;"><strong>Possible Reasons:</strong></p>';
        echo '      <ul style="margin: 0; padding-left: 20px;">';
        echo '        <li>The module does not implement an action for this endpoint.</li>';
        echo '        <li>Required permissions or configuration options are missing.</li>';
        echo '        <li>The module is being updated or re-indexed.</li>';
        echo '      </ul>';
        echo '    </div>';
        echo '    <div style="text-align: center;">';
        echo '      <a href="' . admin_url('setup/modules') . '" target="_top" class="btn btn-primary" style="padding: 8px 20px;"><i class="fa fa-arrow-left"></i> Return to Modules Setup</a>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';

        init_tail();
        return ob_get_clean();
    }
}
