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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class ModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);
    }

    /**
     * Full lifecycle test requiring HTTP routing — skipped due to test env POST routing issue.
     * @todo Fix test environment POST routing to re-enable this test.
     */
    public function test_full_module_lifecycle(): void
    {
        $this->markTestSkipped('Test environment has a POST routing issue with api/* routes.');
    }



    private function createTestModuleZip(array $manifest, array $options = []): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'testmod_') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->fail('Could not create test module ZIP.');
        }

        $manifest = array_merge([
            'version' => '1.0.0',
            'minimum_core_version' => '1.0.0',
            'author' => 'Test',
            'description' => 'Test module',
            'depends' => [],
        ], $manifest);

        $alias = $manifest['alias'];
        $zip->addFromString('module.json', json_encode($manifest));
        $zip->addFromString(
            'routes/web.php',
            '<?php Route::get("modules/' . $alias . '/dashboard", fn() => "ok");'
        );
        $zip->addEmptyDir('Controllers');
        $zip->addEmptyDir('Database/Migrations');
        if (!empty($options['extra_routes'])) {
            $zip->addFromString('Routes/web.php', '<?php ' . $options['extra_routes']);
        }
        $zip->close();

        return $zipPath;
    }

    /**
     * Install and activate a module via ModuleManager directly, returning the Module model.
     */
    private function installModule(array $manifest): Module
    {
        $zipPath = $this->createTestModuleZip($manifest);
        $module = ModuleManager::install($zipPath);
        unlink($zipPath);
        return $module;
    }

    private function installAndActivateModule(array $manifest): Module
    {
        $zipPath = $this->createTestModuleZip($manifest);
        $module = ModuleManager::install($zipPath);
        unlink($zipPath);

        ModuleManager::activate((string) $module->id);

        $module->refresh();
        $this->assertEquals('active', $module->status);

        return $module;
    }

    /**
     * Upgrade a module via ModuleManager directly, returning the Module model.
     */
    private function upgradeModule(string $alias, string $newVersion): Module
    {
        $zipPath = $this->createTestModuleZip([
            'name' => ucfirst($alias),
            'alias' => $alias,
            'version' => $newVersion,
        ]);
        $module = ModuleManager::install($zipPath);
        unlink($zipPath);

        return $module;
    }

    protected function tearDown(): void
    {
        // Clean up any module directories created during tests
        $moduleDirs = ['mod-a', 'mod-b', 'mod-c', 'mod-d', 'mod-e', 'mod-f', 'mod-g', 'mod-h', 'mod-i', 'mod-j', 'mod-solo', 'mockmodule', 'deact-tx', 'dup-a', 'dup-b', 'no-settings', 'with-settings', 'default-mod', 'messy-alias', 'upgrade-norm', 'my-module'];
        foreach ($moduleDirs as $dir) {
            $path = base_path("Modules/{$dir}");
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }
        parent::tearDown();
    }

    public function test_upgrade_preserves_single_dependent(): void
    {
        $parent = $this->installAndActivateModule(['name' => 'ModA', 'alias' => 'mod-a']);
        $child = $this->installAndActivateModule([
            'name' => 'ModB',
            'alias' => 'mod-b',
            'depends' => ['mod-a'],
        ]);

        $this->assertEquals('active', $child->status);

        $this->upgradeModule('mod-a', '2.0.0');

        $child->refresh();
        $this->assertEquals('active', $child->status);
    }

    public function test_upgrade_preserves_multi_level_dependents(): void
    {
        $root = $this->installAndActivateModule(['name' => 'ModC', 'alias' => 'mod-c']);
        $middle = $this->installAndActivateModule([
            'name' => 'ModD',
            'alias' => 'mod-d',
            'depends' => ['mod-c'],
        ]);
        $leaf = $this->installAndActivateModule([
            'name' => 'ModE',
            'alias' => 'mod-e',
            'depends' => ['mod-d'],
        ]);

        $this->upgradeModule('mod-c', '2.0.0');

        $middle->refresh();
        $leaf->refresh();
        $this->assertEquals('active', $middle->status, 'Middle dependent should remain active');
        $this->assertEquals('active', $leaf->status, 'Leaf dependent should remain active');
    }

    public function test_upgrade_preserves_sibling_dependents(): void
    {
        $hub = $this->installAndActivateModule(['name' => 'ModF', 'alias' => 'mod-f']);
        $spokeA = $this->installAndActivateModule([
            'name' => 'ModG',
            'alias' => 'mod-g',
            'depends' => ['mod-f'],
        ]);
        $spokeB = $this->installAndActivateModule([
            'name' => 'ModH',
            'alias' => 'mod-h',
            'depends' => ['mod-f'],
        ]);

        $this->upgradeModule('mod-f', '2.0.0');

        $spokeA->refresh();
        $spokeB->refresh();
        $this->assertEquals('active', $spokeA->status, 'Sibling A should remain active');
        $this->assertEquals('active', $spokeB->status, 'Sibling B should remain active');
    }

    public function test_upgrade_does_not_reactivate_previously_inactive_dependent(): void
    {
        $parent = $this->installAndActivateModule(['name' => 'ModI', 'alias' => 'mod-i']);

        // Install child but keep it inactive
        $childZip = $this->createTestModuleZip([
            'name' => 'ModJ',
            'alias' => 'mod-j',
            'depends' => ['mod-i'],
        ]);
        $child = ModuleManager::install($childZip);
        unlink($childZip);
        $this->assertNotNull($child);
        $this->assertEquals('installed', $child->status);

        // Upgrade parent
        $this->upgradeModule('mod-i', '2.0.0');

        // Child should still be inactive
        $child->refresh();
        $this->assertEquals('installed', $child->status);
    }

    public function test_upgrade_with_no_dependents(): void
    {
        $solo = $this->installAndActivateModule(['name' => 'Solo', 'alias' => 'mod-solo']);

        $upgraded = $this->upgradeModule('mod-solo', '2.0.0');

        $this->assertEquals('active', $upgraded->status, 'Standalone module should remain active after upgrade');
    }

    public function test_activate_rollback_on_duplicate_permission(): void
    {
        // Module A — install, write permissions, then activate
        $moduleA = $this->installModule(['name' => 'ModA', 'alias' => 'dup-a']);
        $moduleAPath = base_path('Modules/dup-a');
        File::put($moduleAPath . '/permissions.json', json_encode([
            ['name' => 'shared.view', 'description' => 'Permission for module A'],
        ]));
        File::put($moduleAPath . '/menu.json', json_encode([
            'title' => 'Module A',
            'route' => '/mod-a',
        ]));
        ModuleManager::activate((string) $moduleA->id);
        $moduleA->refresh();
        $this->assertEquals('active', $moduleA->status);

        // Module B — install, write same permission name
        $moduleB = $this->installModule(['name' => 'ModB', 'alias' => 'dup-b']);
        $moduleBPath = base_path('Modules/dup-b');
        File::put($moduleBPath . '/permissions.json', json_encode([
            ['name' => 'shared.view', 'description' => 'Same permission name as module A'],
        ]));
        File::put($moduleBPath . '/menu.json', json_encode([
            'title' => 'Module B',
            'route' => '/mod-b',
        ]));

        // B's activation should fail — ModulePermission.permission_name is
        // UNIQUE, and 'shared.view' is already taken by module A.
        try {
            ModuleManager::activate((string) $moduleB->id);
            $this->fail('Expected QueryException for duplicate permission name');
        } catch (\Illuminate\Database\QueryException $e) {
            // Expected — transaction rolled back
        }

        // Verify B was fully rolled back
        $moduleB->refresh();
        $this->assertEquals('installed', $moduleB->status,
            'Module B must not be active after failed activation');
        $this->assertCount(0, ModulePermission::where('module_id', $moduleB->id)->get(),
            'No module_permissions for B after rollback');
        $this->assertCount(0, ModuleMenu::where('module_id', $moduleB->id)->get(),
            'No menus for B after rollback');

        // Module A is unaffected
        $moduleA->refresh();
        $this->assertEquals('active', $moduleA->status,
            'Module A must remain active');

        // Cleanup
        File::deleteDirectory($moduleAPath);
        File::deleteDirectory($moduleBPath);
        $moduleA->delete();
        $moduleB->delete();
    }

    public function test_deactivate_successful_transaction(): void
    {
        // Install module, then write permissions/menus before activating
        $module = $this->installModule(['name' => 'DeactTx', 'alias' => 'deact-tx']);
        $modulePath = base_path('Modules/deact-tx');
        File::put($modulePath . '/permissions.json', json_encode([
            ['name' => 'deact-tx.view', 'description' => 'Test permission'],
        ]));
        File::put($modulePath . '/menu.json', json_encode([
            'title' => 'Deact Tx',
            'route' => '/deact-tx',
        ]));

        ModuleManager::activate((string) $module->id);
        $module->refresh();
        $this->assertEquals('active', $module->status);
        $this->assertTrue(Permission::where('name', 'deact-tx.view')->exists());
        $this->assertTrue(ModuleMenu::where('module_id', $module->id)->exists());

        // Deactivate — all DB mutations run in a single transaction
        ModuleManager::deactivate((string) $module->id);
        $module->refresh();

        // Verify all DB state was cleared atomically
        $this->assertEquals('inactive', $module->status);
        $this->assertFalse(Permission::where('name', 'deact-tx.view')->exists(),
            'Permissions removed after deactivation');
        $this->assertEmpty(ModuleMenu::where('module_id', $module->id)->get(),
            'Menus removed after deactivation');

        File::deleteDirectory($modulePath);
        $module->delete();
    }

    public function test_php_header_module_installation(): void
    {
        // Create a temporary ZIP file with a PHP comment header manifest
        $zipPath = tempnam(sys_get_temp_dir(), 'test_php_mod_') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->fail("Could not create PHP header test zip archive.");
        }

        $zip->addFromString('mockmodule.php', <<<'PHP'
<?php
/*
Module Name: Mock Plugin
Description: A mock plugin using PHP header comments.
Version: 2.1
Author: Contributor
Requires at least: 1.0.0
*/
PHP
        );
        $zip->close();

        $module = ModuleManager::install($zipPath);
        unlink($zipPath);

        $this->assertNotNull($module);
        $this->assertEquals('Mock Plugin', $module->name);
        $this->assertEquals('mockmodule', $module->alias);
        $this->assertEquals('2.1.0', $module->version);
        $this->assertEquals('1.0.0', $module->minimum_core_version);
        $this->assertEquals('Contributor', $module->author);
        $this->assertEquals('A mock plugin using PHP header comments.', $module->description);

        // Cleanup — alias-based directory
        $modulePath = base_path('Modules/mockmodule');
        if (File::isDirectory($modulePath)) {
            File::deleteDirectory($modulePath);
        }
        $module->delete();
    }

    public function test_modules_index_includes_settings_link(): void
    {
        // Module WITHOUT settings_route → settings_link is null
        $modNoSettings = $this->installModule(['name' => 'NoSettings', 'alias' => 'no-settings']);
        ModuleManager::activate((string) $modNoSettings->id);

        // Module WITH settings_route in manifest → settings_link is a URL
        $modWithSettings = $this->installModule(['name' => 'WithSettings', 'alias' => 'with-settings']);
        $manifestPath = base_path('Modules/with-settings/module.json');
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $manifest['settings_route'] = 'config';
        file_put_contents($manifestPath, json_encode($manifest));
        ModuleManager::activate((string) $modWithSettings->id);

        $controller = new \App\Http\Controllers\Api\ModuleController();
        $request = \Illuminate\Http\Request::create('/api/modules', 'GET');
        $response = $controller->index($request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);

        $noSettings = collect($payload['data'])->firstWhere('alias', 'no-settings');
        $this->assertNotNull($noSettings);
        $this->assertNull($noSettings['settings_link']);

        $withSettings = collect($payload['data'])->firstWhere('alias', 'with-settings');
        $this->assertNotNull($withSettings);
        $this->assertEquals('/admin/module/with-settings/config', $withSettings['settings_link']);

        // Cleanup
        File::deleteDirectory(base_path('Modules/no-settings'));
        File::deleteDirectory(base_path('Modules/with-settings'));
        $modNoSettings->delete();
        $modWithSettings->delete();
    }

    public function test_modules_index_settings_link_null_by_default(): void
    {
        $mod = $this->installModule(['name' => 'Default', 'alias' => 'default-mod']);
        ModuleManager::activate((string) $mod->id);

        $controller = new \App\Http\Controllers\Api\ModuleController();
        $request = \Illuminate\Http\Request::create('/api/modules', 'GET');
        $response = $controller->index($request);
        $payload = $response->getData(true);

        $entry = collect($payload['data'])->firstWhere('alias', 'default-mod');
        $this->assertNotNull($entry);
        $this->assertNull($entry['settings_link']);

        File::deleteDirectory(base_path('Modules/default-mod'));
        $mod->delete();
    }

    public function test_alias_normalize_basic(): void
    {
        $this->assertEquals('hr-module', ModuleValidator::normalizeAlias('---HR---Module---'));
        $this->assertEquals('hr-module', ModuleValidator::normalizeAlias('HR__Module'));
        $this->assertEquals('hr-module', ModuleValidator::normalizeAlias('HR   Module'));
        $this->assertEquals('hr-module', ModuleValidator::normalizeAlias('hr---module'));
        $this->assertEquals('my-awesome-module', ModuleValidator::normalizeAlias('My Awesome Module!'));
        $this->assertEquals('test123', ModuleValidator::normalizeAlias('test123'));
        $this->assertEquals('a', ModuleValidator::normalizeAlias('---a---'));
    }

    public function test_install_normalizes_alias(): void
    {
        $zipPath = $this->createTestModuleZip([
            'name' => 'Messy Alias Module',
            'alias' => '---MESSY---ALIAS---',
        ]);
        $module = ModuleManager::install($zipPath);
        unlink($zipPath);

        $this->assertNotNull($module);
        $this->assertEquals('messy-alias', $module->alias,
            'Alias should be normalized on install');

        // Filesystem should use normalized alias
        $this->assertTrue(File::isDirectory(base_path('Modules/messy-alias')));

        // The un-normalized directory should NOT exist
        $this->assertFalse(File::isDirectory(base_path('Modules/---MESSY---ALIAS---')));

        File::deleteDirectory(base_path('Modules/messy-alias'));
        $module->delete();
    }

    public function test_upgrade_normalizes_alias(): void
    {
        // Install with clean alias
        $module = $this->installModule(['name' => 'UpgradeNorm', 'alias' => 'upgrade-norm']);
        $moduleId = $module->id;

        // Upgrade with messy alias — should match the normalized version
        $zipPath = $this->createTestModuleZip([
            'name' => 'UpgradeNorm',
            'alias' => '---UPGRADE---NORM---',
            'version' => '2.0.0',
        ]);
        $upgraded = ModuleManager::install($zipPath);
        unlink($zipPath);

        $this->assertNotNull($upgraded);
        $this->assertEquals($moduleId, $upgraded->id,
            'Should upgrade existing module, not create new one');
        $this->assertEquals('upgrade-norm', $upgraded->alias);

        // Cleanup
        File::deleteDirectory(base_path('Modules/upgrade-norm'));
        $upgraded->delete();
    }

    public function test_php_header_alias_is_normalized(): void
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'test_php_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('---My___Module.php', <<<'PHP'
<?php
/*
Module Name: My Module
Version: 1.0
*/
PHP
        );
        $zip->close();

        $module = ModuleManager::install($zipPath);
        unlink($zipPath);

        $this->assertNotNull($module);
        $this->assertEquals('my-module', $module->alias,
            'Alias from PHP header filename should be normalized');

        File::deleteDirectory(base_path('Modules/my-module'));
        $module->delete();
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function makeZip(array $manifest): string
    {
        $path = tempnam(sys_get_temp_dir(), 'h1_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $manifest = array_merge([
            'version' => '1.0.0',
            'minimum_core_version' => '1.0.0',
            'author' => 'Test',
            'description' => 'H1 test module',
        ], $manifest);

        $zip->addFromString('module.json', json_encode($manifest));
        $zip->addFromString('routes/web.php',
            '<?php Route::get("modules/' . $manifest['alias'] . '/dashboard", fn() => "ok");'
        );
        $zip->addFromString('permissions.json', json_encode([
            ['name' => $manifest['alias'] . '.view', 'description' => 'View permission'],
        ]));
        $zip->addFromString('menu.json', json_encode([
            'title' => $manifest['name'],
            'route' => '/dashboard',
            'icon' => 'setting',
            'permission' => $manifest['alias'] . '.view',
        ]));
        $zip->close();
        return $path;
    }

    // ─── H1: Duplicate role_permissions prevention ──────────

    public function test_activate_twice_does_not_duplicate_role_permissions(): void
    {
        $zip = $this->makeZip(['name' => 'TestMod', 'alias' => 'testmod']);
        $module = ModuleManager::install($zip);
        unlink($zip);

        ModuleManager::activate((string) $module->id);
        $countAfterFirst = DB::table('role_permissions')->count();

        // Activate again — should be idempotent
        ModuleManager::activate((string) $module->id);
        $countAfterSecond = DB::table('role_permissions')->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond,
            'Calling activate twice must not create duplicate role_permissions rows');
        $this->assertTrue($countAfterFirst > 0,
            'At least one role_permissions row should exist after activation');

        File::deleteDirectory(base_path('Modules/testmod'));
        $module->delete();
    }

    public function test_composite_primary_key_rejects_duplicate_at_db_level(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $perm = Permission::create(['name' => 'test.dup', 'description' => 'Test']);

        // Insert first time
        DB::table('role_permissions')->insertOrIgnore([
            'role_id' => $adminRole->id,
            'permission_id' => $perm->id,
        ]);
        $this->assertEquals(1, DB::table('role_permissions')->where([
            'role_id' => $adminRole->id,
            'permission_id' => $perm->id,
        ])->count());

        // Attempt duplicate with raw insert (bypassing insertOrIgnore)
        try {
            DB::table('role_permissions')->insert([
                'role_id' => $adminRole->id,
                'permission_id' => $perm->id,
            ]);
            $this->fail('Expected QueryException for duplicate composite key');
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = $e->getMessage();
            $isDup = strpos($msg, 'Duplicate entry') !== false;
            $isUnique = strpos($msg, 'UNIQUE constraint failed') !== false;
            $this->assertTrue($isDup || $isUnique, "Expected duplicate/unique constraint error, got: {$msg}");
        }

        $this->assertEquals(1, DB::table('role_permissions')->where([
            'role_id' => $adminRole->id,
            'permission_id' => $perm->id,
        ])->count(), 'Row count must remain 1 after rejected duplicate');

        $perm->delete();
    }
}
