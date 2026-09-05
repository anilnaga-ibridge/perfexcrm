<?php
// One-shot activation script — requires admin authentication
// Usage: /activate.php?alias=hrm  or  /activate.php?alias=hr-payroll

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

header('Content-Type: application/json');

// Require admin authentication
$user = auth('web')->user() ?? auth('sanctum')->user();
if (!$user || !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: admin authentication required'], JSON_PRETTY_PRINT);
    exit;
}

$alias = $_GET['alias'] ?? null;
if (!$alias) {
    echo json_encode(['error' => 'Pass ?alias=<module_alias>'], JSON_PRETTY_PRINT);
    exit;
}

// Validate alias format to prevent path traversal
if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $alias) && strlen($alias) > 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid module alias format'], JSON_PRETTY_PRINT);
    exit;
}

try {
    $module = \App\Models\Module::where('alias', $alias)->firstOrFail();
    
    if ($module->status === 'active') {
        if (!isset($_GET['reactivate'])) {
            echo json_encode([
                'info'   => 'Already active',
                'module' => $module->toArray(),
                'menus'  => \App\Models\ModuleMenu::where('module_id', $module->id)->get()->toArray(),
            ], JSON_PRETTY_PRINT);
            exit;
        }
        // Force deactivate then reactivate to pick up updated menu.json
        \App\Services\ModuleManager::deactivate($module->id);
        $module->refresh();
    }

    $result = \App\Services\ModuleManager::activate($module->id);

    echo json_encode([
        'success' => true,
        'module'  => $result->toArray(),
        'menus'   => \App\Models\ModuleMenu::where('module_id', $result->id)->get()->toArray(),
    ], JSON_PRETTY_PRINT);

} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    // Module not in DB — register it first from disk
    $manifestPath = base_path("Modules/{$alias}/module.json");
    if (!file_exists($manifestPath)) {
        echo json_encode(['error' => "module.json not found for alias '{$alias}'"], JSON_PRETTY_PRINT);
        exit;
    }
    $info = json_decode(file_get_contents($manifestPath), true);
    $module = \App\Models\Module::create([
        'name'                 => $info['name'] ?? $alias,
        'alias'                => $alias,
        'version'              => $info['version'] ?? '1.0.0',
        'minimum_core_version' => $info['minimum_core_version'] ?? '1.0.0',
        'depends'              => $info['depends'] ?? [],
        'description'          => $info['description'] ?? '',
        'status'               => 'installed',
        'author'               => $info['author'] ?? '',
    ]);
    $result = \App\Services\ModuleManager::activate($module->id);
    echo json_encode([
        'success' => true,
        'created' => true,
        'module'  => $result->toArray(),
        'menus'   => \App\Models\ModuleMenu::where('module_id', $result->id)->get()->toArray(),
    ], JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
