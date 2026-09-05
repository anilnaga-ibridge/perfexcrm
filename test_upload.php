<?php

// 1. Boot Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ModuleManager;
use App\Models\Module;
use App\Models\ModuleMenu;
use App\Models\ModulePermission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

$alias = 'mock-test-module';
$name = 'Mock Test Module';

echo "=== STEP 1: CLEANING UP PREVIOUS RUNS ===\n";
// Clean database
$mod = Module::where('alias', $alias)->first();
if ($mod) {
    echo "Found existing module in DB, uninstalling...\n";
    try {
        ModuleManager::uninstall($mod->id, true);
    } catch (\Exception $e) {
        echo "Uninstall failed: " . $e->getMessage() . "\n";
    }
}
// Clean filesystem
$modulePath = base_path("Modules/{$alias}");
if (File::exists($modulePath)) {
    File::deleteDirectory($modulePath);
}
$zipPath = base_path("packages/{$alias}-1.0.0.zip");
if (File::exists($zipPath)) {
    File::delete($zipPath);
}

echo "=== STEP 2: CREATING NEW TEST MODULE ===\n";
Artisan::call('module:make', [
    'name' => $name,
    '--crud' => true,
    '--settings' => true,
]);
echo "Module created at Modules/{$alias}\n";

// Inject some strict compliance files (like README, LICENSE, settings.json)
File::ensureDirectoryExists($modulePath);
File::put($modulePath . '/README.md', '# Mock Test Module');
File::put($modulePath . '/LICENSE', 'MIT License');
File::ensureDirectoryExists($modulePath . '/lang/en');
File::put($modulePath . '/lang/en/messages.php', '<?php return [];');
File::ensureDirectoryExists($modulePath . '/tests/Feature');
File::put($modulePath . '/tests/Feature/ExampleTest.php', '<?php
namespace Modules\MockTestModule\Tests\Feature;
use Tests\TestCase;
class ExampleTest extends TestCase {
    public function test_example() { $this->assertTrue(true); }
}');

$manifestPath = $modulePath . '/module.json';
if (File::exists($manifestPath)) {
    $manifest = json_decode(File::get($manifestPath), true);
    $manifest['sdk_version'] = '1.0';
    $manifest['settings'] = ['schema' => 'settings.json'];
    File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
}

$settingsPath = $modulePath . '/settings.json';
$validSettingsSchema = [
    'sections' => [
        [
            'title' => 'General Settings',
            'fields' => [
                [
                    'key' => 'hw_api_url',
                    'type' => 'text',
                    'label' => 'API Endpoint',
                    'default' => 'https://api.example.com'
                ]
            ]
        ]
    ]
];
File::put($settingsPath, json_encode($validSettingsSchema, JSON_PRETTY_PRINT));

echo "=== STEP 3: PACKAGING MODULE TO ZIP ===\n";
Artisan::call('module:package', [
    'module' => $alias,
]);
if (File::exists($zipPath)) {
    echo "Packaged ZIP successfully: {$zipPath}\n";
} else {
    echo "ERROR: Failed to package ZIP!\n";
    exit(1);
}

// Remove local directory to simulate upload & install
File::deleteDirectory($modulePath);

echo "=== STEP 4: UPLOADING & INSTALLING ZIP ===\n";
$module = ModuleManager::install($zipPath);
echo "Module installed successfully!\n";
echo "DB ID: " . $module->id . "\n";
echo "Status: " . $module->status . "\n";

echo "=== STEP 5: ACTIVATING MODULE ===\n";
$module = ModuleManager::activate($module->id);
echo "Module activated successfully!\n";
echo "Status: " . $module->status . "\n";

echo "=== STEP 6: VERIFYING DATABASE RECORDS ===\n";
$dbMod = Module::where('alias', $alias)->first();
if ($dbMod && $dbMod->status === 'active') {
    echo "SUCCESS: Module is active in database.\n";
} else {
    echo "ERROR: Module not found or inactive in database.\n";
}

$menus = ModuleMenu::where('module_id', $module->id)->get();
echo "Registered Menus count: " . count($menus) . "\n";
foreach ($menus as $m) {
    echo "  - Title: " . $m->title . " | Route: " . $m->route . "\n";
}

$perms = DB::table('module_permissions')->where('module_id', $module->id)->get();
echo "Registered Permissions count: " . count($perms) . "\n";
foreach ($perms as $p) {
    echo "  - Permission: " . $p->permission_name . "\n";
}

echo "=== STEP 7: CLEANING UP TEST MODULE ===\n";
ModuleManager::uninstall($module->id, true);
echo "Module uninstalled successfully!\n";
echo "=== TEST COMPLETED SUCCESSFULLY ===\n";
