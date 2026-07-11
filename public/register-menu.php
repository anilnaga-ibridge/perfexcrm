<?php
// Direct menu registration tool — bypass activate() early-exit for already-active modules
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = \Illuminate\Http\Request::capture());

header('Content-Type: application/json');

$alias  = $_GET['alias'] ?? null;
$action = $_GET['action'] ?? 'status';

if (!$alias) {
    echo json_encode(['error' => 'Pass ?alias=<module_alias>'], JSON_PRETTY_PRINT);
    exit;
}

try {
    $module = \App\Models\Module::where('alias', $alias)->firstOrFail();

    if ($action === 'status') {
        $menus = \DB::table('module_menus')->where('module_id', $module->id)->get();
        echo json_encode([
            'module' => $module->toArray(),
            'menus'  => $menus,
        ], JSON_PRETTY_PRINT);
        exit;
    }

    if ($action === 'register-menus') {
        // Read menu.json from the module directory
        $modulePath = base_path("Modules/{$alias}");
        $menuPath   = "{$modulePath}/menu.json";

        if (!file_exists($menuPath)) {
            echo json_encode(['error' => "No menu.json found at: Modules/{$alias}/menu.json"], JSON_PRETTY_PRINT);
            exit;
        }

        $menu = json_decode(file_get_contents($menuPath), true);
        if (!$menu) {
            echo json_encode(['error' => 'menu.json is invalid JSON'], JSON_PRETTY_PRINT);
            exit;
        }

        \DB::transaction(function () use ($module, $menu) {
            // Remove old menus for this module
            \DB::table('module_menus')->where('module_id', $module->id)->delete();
            // Re-register from menu.json
            \App\Services\ModuleManager::registerMenuNode($module->id, $menu);
        });

        $menus = \DB::table('module_menus')->where('module_id', $module->id)->get();
        echo json_encode([
            'success' => true,
            'module'  => $module->fresh()->toArray(),
            'menus'   => $menus,
        ], JSON_PRETTY_PRINT);
        exit;
    }

    if ($action === 'force-reactivate') {
        // Deactivate first (sets status to inactive, removes menus)
        \App\Services\ModuleManager::deactivate($module->id);
        $module->refresh();
        // Now activate (will re-read menu.json)
        $result = \App\Services\ModuleManager::activate($module->id);
        $menus  = \DB::table('module_menus')->where('module_id', $module->id)->get();
        echo json_encode([
            'success' => true,
            'module'  => $result->toArray(),
            'menus'   => $menus,
        ], JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode(['error' => 'Unknown action. Use: status, register-menus, force-reactivate'], JSON_PRETTY_PRINT);

} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    echo json_encode(['error' => "Module '{$alias}' not found in DB"], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
