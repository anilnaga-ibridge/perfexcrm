<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleMenu;
use App\Services\ModuleManager;
use App\Services\ModuleSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\File;

class ModuleController extends Controller
{
    /**
     * List all modules.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Module::query();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $settingsService = app(\App\Services\ModuleSettingsService::class);
        $modules = $query->orderBy('name')->get()->map(function ($mod) use ($settingsService) {
            // Frontend backward compatibility mapping
            $mod->is_active = ($mod->status === 'active');
            $mod->is_installed = true;
            $mod->slug = $mod->alias;
            $mod->settings_link = $this->getSettingsLink($mod);
            $mod->has_settings = $settingsService->hasSettings($mod);
            return $mod;
        });

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    /**
     * Derive the settings link from the module's manifest.
     * Returns null when the module does not declare a settings route.
     */
    private function getSettingsLink(Module $module): ?string
    {
        $manifestPath = base_path("Modules/{$module->alias}/module.json");
        if (!File::exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(File::get($manifestPath), true);
        if (!is_array($manifest) || empty($manifest['settings_route'])) {
            return null;
        }

        return '/admin/module/' . $module->alias . '/' . ltrim($manifest['settings_route'], '/');
    }

    /**
     * Store and install a newly uploaded module ZIP.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'module_file' => 'required|file|mimes:zip',
        ]);

        try {
            $file = $request->file('module_file');
            $tempPath = $file->storeAs('temp_uploads', $file->getClientOriginalName(), 'local');
            $fullTempPath = Storage::disk('local')->path($tempPath);

            $module = ModuleManager::install($fullTempPath);

            // Auto-activate immediately so menus appear in the sidebar right away.
            // ModuleManager::activate() is idempotent — safe to call even if already active.
            try {
                $module = ModuleManager::activate($module->id);
            } catch (Exception $activateEx) {
                // Activation failure is non-fatal — plugin is installed, user can activate manually.
                \Illuminate\Support\Facades\Log::warning("Auto-activate failed for plugin [{$module->alias}]: " . $activateEx->getMessage());
            }

            // Clean up temporary upload
            if (file_exists($fullTempPath)) {
                unlink($fullTempPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Plugin uploaded, installed, and activated successfully.',
                'data'    => $module,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Activate a module.
     */
    public function activate($id): JsonResponse
    {
        try {
            $module = ModuleManager::activate($id);
            return response()->json([
                'success' => true,
                'message' => 'Module activated successfully.',
                'data' => $module,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Deactivate a module.
     */
    public function deactivate($id): JsonResponse
    {
        try {
            $module = ModuleManager::deactivate($id);
            return response()->json([
                'success' => true,
                'message' => 'Module deactivated successfully.',
                'data' => $module,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Toggle module active/inactive status.
     */
    public function toggleStatus($id): JsonResponse
    {
        $module = Module::findOrFail($id);
        if ($module->status === 'active') {
            return $this->deactivate($id);
        } else {
            return $this->activate($id);
        }
    }

    /**
     * Get active modules list with frontend backward compatibility mapping.
     */
    public function active(): JsonResponse
    {
        $settingsService = app(ModuleSettingsService::class);

        $modules = Module::where('status', 'active')->get()->map(function ($mod) use ($settingsService) {
            $settingsRoute = null;
            $manifestPath = base_path("Modules/{$mod->alias}/module.json");
            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                if (is_array($manifest) && !empty($manifest['settings_route'])) {
                    $settingsRoute = $manifest['settings_route'];
                }
            }

            return [
                'id' => $mod->id,
                'name' => $mod->name,
                'slug' => $mod->alias,
                'is_active' => true,
                'sidebar_label' => $mod->name,
                'sidebar_path' => "/admin/module/{$mod->alias}/dashboard",
                'sidebar_icon' => $mod->icon,
                'settings_route' => $settingsRoute,
                'settings_link' => $this->getSettingsLink($mod),
                'has_settings' => $settingsService->hasSettings($mod),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    /**
     * Fetch relational module menus filtered on-the-fly by user authorization permissions.
     */
    public function menus(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $activeModules = Module::where('status', 'active')->get();
        $menuList = [];

        foreach ($activeModules as $mod) {
            // Fetch root menus
            $rootMenus = ModuleMenu::where('module_id', $mod->id)
                ->whereNull('parent_id')
                ->get();

            foreach ($rootMenus as $root) {
                // If a root permission is defined, check if user has it
                if ($root->permission && !$user->hasPermission($root->permission)) {
                    continue;
                }

                $children = ModuleMenu::where('parent_id', $root->id)->get()->filter(function ($child) use ($user) {
                    if ($child->permission && !$user->hasPermission($child->permission)) {
                        return false;
                    }
                    return true;
                })->map(function ($child) use ($mod) {
                    return [
                        'name' => \App\Services\ModuleManager::formatMenuTitle($child->title),
                        'path' => "/admin/module/{$mod->alias}" . $child->route,
                    ];
                })->values()->toArray();

                $menuList[] = [
                    'name' => \App\Services\ModuleManager::formatMenuTitle($root->title),
                    'icon' => $root->icon,
                    'path' => $root->route ? "/admin/module/{$mod->alias}" . $root->route : null,
                    'children' => !empty($children) ? $children : null,
                    'dynamic' => true,
                    'slug' => $mod->alias,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $menuList,
        ]);
    }

    /**
     * Generate an SSO URL for a dynamic module iframe.
     */
    public function ssoUrl(Request $request)
    {
        try {
            $request->validate([
                'redirect' => 'required|string'
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'No authenticated user on request'], 401);
            }

            $token = Str::random(64);

            Cache::put(
                'sso_token_'.$token,
                [
                    'user_id' => $user->id,
                    'redirect' => $request->redirect,
                ],
                now()->addMinute()
            );

            return response()->json([
                'url' => url('/plugins/sso').'?token='.$token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Handle the SSO web login request from the iframe.
     */
    public function ssoLogin(Request $request)
    {
        $token = $request->query('token');

        $payload = Cache::pull(
            'sso_token_'.$token
        );

        if (!$payload) {
            abort(403, 'Invalid SSO token');
        }

        // Redirect validation to prevent open redirect vulnerabilities
        $allowedPrefixes = [
            '/modules/',
            '/plugins/',
        ];

        $redirectOk = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($payload['redirect'], $prefix)) {
                $redirectOk = true;
                break;
            }
        }

        if (!$redirectOk) {
            abort(403, 'Invalid redirect path');
        }

        auth('web')->loginUsingId(
            $payload['user_id']
        );

        session()->regenerate();

        return redirect(
            $payload['redirect']
        );
    }

    /**
     * Delete and uninstall a module.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $deleteData = filter_var($request->query('delete_data', false), FILTER_VALIDATE_BOOLEAN);
            ModuleManager::uninstall($id, $deleteData);

            return response()->json([
                'success' => true,
                'message' => 'Module uninstalled successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Fetch module settings schema and current values.
     */
    public function getSettings(string $alias, ModuleSettingsService $settingsService): JsonResponse
    {
        $module = Module::where('alias', $alias)->firstOrFail();
        $settings = $settingsService->getSettings($module);
        if ($settings === null) {
            return response()->json([
                'success' => false,
                'message' => "Module '{$alias}' does not declare a settings schema.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Save module settings values.
     */
    public function saveSettings(Request $request, string $alias, ModuleSettingsService $settingsService): JsonResponse
    {
        $module = Module::where('alias', $alias)->firstOrFail();

        try {
            $updatedValues = $settingsService->save($module, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully.',
                'data' => $updatedValues,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset module settings to defaults.
     */
    public function resetSettings(string $alias, ModuleSettingsService $settingsService): JsonResponse
    {
        $module = Module::where('alias', $alias)->firstOrFail();

        try {
            $defaultValues = $settingsService->resetToDefaults($module);
            $schema = $settingsService->getSchema($module);
            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully.',
                'data' => [
                    'schema' => $schema,
                    'values' => $defaultValues,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Repair a module.
     */
    public function repair($id): JsonResponse
    {
        try {
            $module = ModuleManager::repair($id);
            return response()->json([
                'success' => true,
                'message' => 'Module repaired successfully.',
                'data' => $module,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Rollback a module.
     */
    public function rollback($id): JsonResponse
    {
        try {
            $module = ModuleManager::rollback($id);
            return response()->json([
                'success' => true,
                'message' => 'Module rolled back successfully.',
                'data' => $module,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
