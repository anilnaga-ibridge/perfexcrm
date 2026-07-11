<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Module;
use App\Models\ModuleMenu;
use App\Models\ModulePermission;
use App\Models\Permission;
use App\Services\ModuleManager;
use App\Services\ModuleValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class ModuleEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private const CORE_VERSION = '3.0.0';

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);
    }

    protected function tearDown(): void
    {
        $dirs = ['payroll', 'reports', 'analytics', 'tax', 'full-cycle', 'dup-fail-a', 'dup-fail-b', 'my-module', 'crm', 'hr-module', 'my-old-module', 'My Old Module'];
        foreach ($dirs as $dir) {
            $path = base_path("Modules/{$dir}");
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }
        parent::tearDown();
    }

    // ─── Helpers ───────────────────────────────────────────────

    private function makeZip(array $manifest, array $extras = []): string
    {
        $path = tempnam(sys_get_temp_dir(), 'e2e_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $manifest = array_merge([
            'version' => '1.0.0',
            'minimum_core_version' => '1.0.0',
            'author' => 'Test',
            'description' => 'E2E test module',
        ], $manifest);

        $zip->addFromString('module.json', json_encode($manifest));
        $zip->addFromString('routes/web.php',
            '<?php Route::get("modules/' . $manifest['alias'] . '/dashboard", fn() => "ok");'
        );

        if (!empty($extras['permissions'])) {
            $zip->addFromString('permissions.json', json_encode($extras['permissions']));
        } else {
            $zip->addFromString('permissions.json', json_encode([
                ['name' => $manifest['alias'] . '.view', 'description' => 'View permission'],
            ]));
        }

        if (!empty($extras['menu'])) {
            $zip->addFromString('menu.json', json_encode($extras['menu']));
        } else {
            $zip->addFromString('menu.json', json_encode([
                'title' => $manifest['name'],
                'route' => '/dashboard',
                'icon' => 'setting',
                'permission' => $manifest['alias'] . '.view',
            ]));
        }

        if (!empty($extras['settings_route'])) {
            $manifest['settings_route'] = $extras['settings_route'];
            // Re-write module.json with settings_route
            $zip->addFromString('module.json', json_encode($manifest));
            // Update the local reference so makeZip can return it for assertions
        }

        $zip->close();
        return $path;
    }

    private function assertModuleState(string $alias, string $expectedStatus, array $extra = []): Module
    {
        $m = Module::where('alias', $alias)->first();
        $this->assertNotNull($m, "Module '{$alias}' not found in DB");
        $this->assertEquals($expectedStatus, $m->status, "Module '{$alias}' status mismatch");

        if ($expectedStatus === 'active' || $expectedStatus === 'installed') {
            $this->assertTrue(File::isDirectory(base_path("Modules/{$alias}")),
                "Directory Modules/{$alias} should exist");
        }

        if ($expectedStatus === 'active') {
            $permName = $alias . '.view';
            $this->assertTrue(Permission::where('name', $permName)->exists(),
                "Permission '{$permName}' should exist for active module");
            $this->assertTrue(ModuleMenu::where('module_id', $m->id)->exists(),
                "Menus should exist for active module '{$alias}'");
        }

        if ($expectedStatus === 'inactive' || $expectedStatus === 'installed') {
            $permName = $alias . '.view';
            $this->assertFalse(Permission::where('name', $permName)->exists(),
                "Permission '{$permName}' should NOT exist for inactive module");
            $this->assertFalse(ModuleMenu::where('module_id', $m->id)->exists(),
                "Menus should NOT exist for inactive module '{$alias}'");
        }

        if (isset($extra['settings_link'])) {
            $controller = new \App\Http\Controllers\Api\ModuleController();
            $request = \Illuminate\Http\Request::create('/api/modules', 'GET');
            $response = $controller->index($request);
            $payload = $response->getData(true);
            $entry = collect($payload['data'])->firstWhere('alias', $alias);
            $this->assertNotNull($entry, "Module '{$alias}' should be in index response");
            $this->assertEquals($extra['settings_link'], $entry['settings_link'],
                "settings_link mismatch for '{$alias}'");
        }

        return $m;
    }

    // ─── Scenario 1: Full lifecycle ────────────────────────────

    public function test_scenario_1_full_lifecycle(): void
    {
        // 1. Install
        $zip = $this->makeZip(
            ['name' => 'FullCycle', 'alias' => 'full-cycle'],
            ['settings_route' => 'config']
        );
        $m = ModuleManager::install($zip);
        unlink($zip);

        $this->assertNotNull($m);
        $this->assertModuleState('full-cycle', 'installed');

        // 2. Activate
        ModuleManager::activate((string) $m->id);
        $this->assertModuleState('full-cycle', 'active', ['settings_link' => '/admin/module/full-cycle/config']);

        // 3. Deactivate
        ModuleManager::deactivate((string) $m->id);
        $this->assertModuleState('full-cycle', 'inactive');

        // 4. Reactivate
        ModuleManager::activate((string) $m->id);
        $this->assertModuleState('full-cycle', 'active', ['settings_link' => '/admin/module/full-cycle/config']);

        // 5. Upgrade
        $zip2 = $this->makeZip(
            ['name' => 'FullCycle', 'alias' => 'full-cycle', 'version' => '2.0.0'],
            ['settings_route' => 'config']
        );
        $upgraded = ModuleManager::install($zip2);
        unlink($zip2);

        $this->assertEquals($m->id, $upgraded->id);
        $this->assertModuleState('full-cycle', 'active', ['settings_link' => '/admin/module/full-cycle/config']);
        $this->assertEquals('2.0.0', $upgraded->version);

        // 6. Uninstall
        ModuleManager::uninstall((string) $m->id, false);
        $this->assertNull(Module::where('alias', 'full-cycle')->first());
        $this->assertFalse(File::isDirectory(base_path('Modules/full-cycle')));
        $this->assertFalse(Permission::where('name', 'full-cycle.view')->exists());
    }

    // ─── Scenario 2: Dependency tree upgrade ───────────────────

    public function test_scenario_2_dependency_tree(): void
    {
        // Payroll
        $zipP = $this->makeZip(['name' => 'Payroll', 'alias' => 'payroll']);
        $payroll = ModuleManager::install($zipP);
        unlink($zipP);
        ModuleManager::activate((string) $payroll->id);
        $this->assertModuleState('payroll', 'active');

        // Reports depends on Payroll
        $zipR = $this->makeZip(['name' => 'Reports', 'alias' => 'reports', 'depends' => ['payroll']]);
        $reports = ModuleManager::install($zipR);
        unlink($zipR);
        ModuleManager::activate((string) $reports->id);
        $this->assertModuleState('reports', 'active');

        // Analytics depends on Reports
        $zipA = $this->makeZip(['name' => 'Analytics', 'alias' => 'analytics', 'depends' => ['reports']]);
        $analytics = ModuleManager::install($zipA);
        unlink($zipA);
        ModuleManager::activate((string) $analytics->id);
        $this->assertModuleState('analytics', 'active');

        // Tax depends on Payroll (sibling to Reports)
        $zipT = $this->makeZip(['name' => 'Tax', 'alias' => 'tax', 'depends' => ['payroll']]);
        $tax = ModuleManager::install($zipT);
        unlink($zipT);
        ModuleManager::activate((string) $tax->id);
        $this->assertModuleState('tax', 'active');

        // Upgrade Payroll
        $zipP2 = $this->makeZip(['name' => 'Payroll', 'alias' => 'payroll', 'version' => '2.0.0']);
        ModuleManager::install($zipP2);
        unlink($zipP2);

        // All modules must remain active
        $this->assertModuleState('payroll', 'active');
        $this->assertModuleState('reports', 'active');
        $this->assertModuleState('analytics', 'active');
        $this->assertModuleState('tax', 'active');

        // Menus and permissions preserved for all
        foreach (['payroll', 'reports', 'analytics', 'tax'] as $alias) {
            $mod = Module::where('alias', $alias)->first();
            $this->assertTrue(ModuleMenu::where('module_id', $mod->id)->exists(),
                "Menus preserved for {$alias}");
            $this->assertTrue(Permission::where('name', "{$alias}.view")->exists(),
                "Permission preserved for {$alias}");
        }
    }

    // ─── Scenario 3: Inactive dependent stays inactive ─────────

    public function test_scenario_3_inactive_dependent_stays_inactive(): void
    {
        // Payroll active
        $zipP = $this->makeZip(['name' => 'Payroll', 'alias' => 'payroll']);
        $payroll = ModuleManager::install($zipP);
        unlink($zipP);
        ModuleManager::activate((string) $payroll->id);

        // Reports installed but NOT activated
        $zipR = $this->makeZip(['name' => 'Reports', 'alias' => 'reports', 'depends' => ['payroll']]);
        $reports = ModuleManager::install($zipR);
        unlink($zipR);
        // reports stays 'installed'

        // Upgrade Payroll
        $zipP2 = $this->makeZip(['name' => 'Payroll', 'alias' => 'payroll', 'version' => '2.0.0']);
        ModuleManager::install($zipP2);
        unlink($zipP2);

        $this->assertModuleState('payroll', 'active');
        $this->assertModuleState('reports', 'installed');
    }

    // ─── Scenario 4: Activation rollback ───────────────────────

    public function test_scenario_4_activation_rollback(): void
    {
        // Module A — active with default permissions (includes 'dup-fail-a.view')
        // plus an extra 'shared.view' to trigger the conflict.
        $zipA = $this->makeZip(
            ['name' => 'DupFailA', 'alias' => 'dup-fail-a'],
            ['permissions' => [
                ['name' => 'dup-fail-a.view', 'description' => 'A view'],
                ['name' => 'shared.view', 'description' => 'Shared'],
            ]]
        );
        $modA = ModuleManager::install($zipA);
        unlink($zipA);
        ModuleManager::activate((string) $modA->id);
        $this->assertModuleState('dup-fail-a', 'active');

        // Module B — install; permissions.json includes 'shared.view' (already taken)
        $zipB = $this->makeZip(
            ['name' => 'DupFailB', 'alias' => 'dup-fail-b'],
            ['permissions' => [
                ['name' => 'dup-fail-b.view', 'description' => 'B view'],
                ['name' => 'shared.view', 'description' => 'Conflict'],
            ]]
        );
        $modB = ModuleManager::install($zipB);
        unlink($zipB);
        $this->assertModuleState('dup-fail-b', 'installed');

        // Activation must fail
        try {
            ModuleManager::activate((string) $modB->id);
            $this->fail('Expected QueryException for duplicate permission');
        } catch (\Illuminate\Database\QueryException $e) {
            // Expected
        }

        // B fully rolled back
        $this->assertModuleState('dup-fail-b', 'installed');
        $this->assertCount(0, ModulePermission::where('module_id', $modB->id)->get());
        $this->assertCount(0, ModuleMenu::where('module_id', $modB->id)->get());

        // A unaffected
        $this->assertModuleState('dup-fail-a', 'active');
    }

    // ─── Scenario 5: Messy aliases ─────────────────────────────

    public function test_scenario_5_messy_aliases(): void
    {
        $modules = [];

        // "My___Module" → normalized to "my-module"
        $zip = $this->makeZip(['name' => 'My Module', 'alias' => 'My___Module']);
        $modules[] = ModuleManager::install($zip);
        unlink($zip);

        // "---CRM---" → normalized to "crm"
        $zip = $this->makeZip(['name' => 'CRM Extension', 'alias' => '---CRM---']);
        $modules[] = ModuleManager::install($zip);
        unlink($zip);

        // "HR Module" → normalized to "hr-module"
        $zip = $this->makeZip(['name' => 'HR Module', 'alias' => 'HR Module']);
        $modules[] = ModuleManager::install($zip);
        unlink($zip);

        // Verify normalized aliases are stored
        $this->assertNotNull(Module::where('alias', 'my-module')->first());
        $this->assertNotNull(Module::where('alias', 'crm')->first());
        $this->assertNotNull(Module::where('alias', 'hr-module')->first());

        // Original messy names should NOT exist
        $this->assertNull(Module::where('alias', 'My___Module')->first());
        $this->assertNull(Module::where('alias', '---CRM---')->first());
        $this->assertNull(Module::where('alias', 'HR Module')->first());

        // Filesystem uses normalized paths
        $this->assertTrue(File::isDirectory(base_path('Modules/my-module')));
        $this->assertTrue(File::isDirectory(base_path('Modules/crm')));
        $this->assertTrue(File::isDirectory(base_path('Modules/hr-module')));

        // Activate and verify all lifecycle ops work with normalized alias
        // Note: permissions/menus in each zip embed the original messy alias string
        // (e.g., 'My___Module.view'), not the normalized one — so assertModuleState
        // (which checks {alias}.view) isn't used here. We check status + directory directly.
        foreach (['my-module', 'crm', 'hr-module'] as $alias) {
            $m = Module::where('alias', $alias)->first();

            ModuleManager::activate((string) $m->id);
            $m->refresh();
            $this->assertEquals('active', $m->status);
            $this->assertTrue(File::isDirectory(base_path("Modules/{$alias}")),
                "Directory Modules/{$alias} should exist after activation");

            ModuleManager::deactivate((string) $m->id);
            $m->refresh();
            $this->assertEquals('inactive', $m->status);
            $this->assertTrue(File::isDirectory(base_path("Modules/{$alias}")),
                "Directory Modules/{$alias} should exist after deactivation");
        }
    }

    // ─── Scenario 6: Legacy module backward compatibility ──────

    public function test_scenario_6_legacy_module(): void
    {
        // Simulate legacy module: name-based directory and alias-based DB entry
        $legacyPath = base_path('Modules/My Old Module');
        File::ensureDirectoryExists($legacyPath);
        File::put($legacyPath . '/module.json', json_encode([
            'name' => 'My Old Module',
            'alias' => 'my-old-module',
            'version' => '1.0.0',
            'minimum_core_version' => '1.0.0',
            'author' => 'Legacy Author',
            'description' => 'A legacy module',
        ]));
        File::ensureDirectoryExists($legacyPath . '/Controllers');
        File::ensureDirectoryExists($legacyPath . '/routes');
        File::put($legacyPath . '/routes/web.php', '<?php Route::get("modules/my-old-module/dashboard", fn() => "ok");');
        File::put($legacyPath . '/permissions.json', json_encode([
            ['name' => 'my-old-module.view', 'description' => 'View permission'],
        ]));
        File::put($legacyPath . '/menu.json', json_encode([
            'title' => 'My Old Module',
            'route' => '/dashboard',
            'icon' => 'setting',
            'permission' => 'my-old-module.view',
        ]));

        // Manually create the DB record (simulating legacy installation)
        $module = Module::create([
            'name' => 'My Old Module',
            'alias' => 'my-old-module',
            'version' => '1.0.0',
            'minimum_core_version' => '1.0.0',
            'status' => 'installed',
        ]);

        // Activate — should find the module via legacy fallback
        ModuleManager::activate((string) $module->id);
        $module->refresh();

        $this->assertEquals('active', $module->status);
        $this->assertTrue(File::isDirectory($legacyPath),
            'Legacy name-based directory must still exist');
        $this->assertTrue(Permission::where('name', 'my-old-module.view')->exists(),
            'Permissions created from legacy module');
        $this->assertTrue(ModuleMenu::where('module_id', $module->id)->exists(),
            'Menus created from legacy module');

        // Deactivate
        ModuleManager::deactivate((string) $module->id);
        $module->refresh();
        $this->assertEquals('inactive', $module->status);

        // Upgrade
        $zip = $this->makeZip(['name' => 'My Old Module', 'alias' => 'my-old-module', 'version' => '2.0.0']);
        $upgraded = ModuleManager::install($zip);
        unlink($zip);

        $this->assertEquals($module->id, $upgraded->id);
        $this->assertEquals('2.0.0', $upgraded->version);

        // After upgrade, files should be under alias-based path
        $aliasPath = base_path('Modules/my-old-module');
        $this->assertTrue(File::isDirectory($aliasPath),
            'After upgrade, alias-based directory should exist');

        // Legacy name-based directory should be cleaned up
        $this->assertFalse(File::isDirectory($legacyPath),
            'Legacy name-based directory should be removed after upgrade');

        // Cleanup
        File::deleteDirectory($aliasPath);
        $upgraded->delete();
    }
}
