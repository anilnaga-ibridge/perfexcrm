<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Module;
use App\Models\ModuleMenu;
use App\Models\Permission;
use App\Services\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ModuleLifecycleTest extends TestCase
{
    use RefreshDatabase;
    use ModuleTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed standard roles
        \App\Models\Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);
        
        $this->cleanModuleDir();
    }

    protected function tearDown(): void
    {
        $this->cleanModuleDir();
        parent::tearDown();
    }

    public function test_module_can_be_installed_activated_deactivated_and_uninstalled(): void
    {
        // 1. Package a dummy module
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->makeStrictCompliant();
        
        Artisan::call('module:package', [
            'module' => $this->testAlias,
        ]);

        $zipPath = base_path("packages/{$this->testAlias}-1.0.0.zip");
        $this->assertFileExists($zipPath);

        // Remove local directory to simulate upload & install
        File::deleteDirectory($this->getModulePath());
        $this->assertDirectoryDoesNotExist($this->getModulePath());

        // 2. Install
        $module = ModuleManager::install($zipPath);
        $this->assertInstanceOf(Module::class, $module);
        $this->assertEquals($this->testAlias, $module->alias);
        $this->assertEquals('installed', $module->status);
        $this->assertDirectoryExists($this->getModulePath());

        // Verify database entry
        $this->assertDatabaseHas('modules', [
            'alias' => $this->testAlias,
            'status' => 'installed',
        ]);

        // 3. Activate
        $module = ModuleManager::activate($module->id);
        $this->assertEquals('active', $module->status);

        // Verify menus and permissions loaded
        $this->assertDatabaseHas('modules', [
            'alias' => $this->testAlias,
            'status' => 'active',
        ]);
        
        // Verify mock_test_module_view permission created
        $this->assertDatabaseHas('permissions', [
            'name' => 'mock_test_module_view',
        ]);

        // Verify menu registered
        $this->assertDatabaseHas('module_menus', [
            'module_id' => $module->id,
            'title' => 'Mock Test Module',
        ]);

        // 4. Deactivate
        $module = ModuleManager::deactivate($module->id);
        $this->assertEquals('inactive', $module->status);

        // Verify menus and permissions removed
        $this->assertDatabaseMissing('permissions', [
            'name' => 'mock_test_module_view',
        ]);
        $this->assertDatabaseMissing('module_menus', [
            'module_id' => $module->id,
            'title' => 'Mock Test Module',
        ]);

        // 5. Uninstall
        ModuleManager::uninstall($module->id, true);
        $this->assertDirectoryDoesNotExist($this->getModulePath());
        $this->assertDatabaseMissing('modules', [
            'alias' => $this->testAlias,
        ]);
    }
}
