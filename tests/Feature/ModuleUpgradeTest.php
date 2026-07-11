<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Module;
use App\Models\ModuleMenu;
use App\Models\Permission;
use App\Services\ModuleManager;
use App\Services\ModuleSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ModuleUpgradeTest extends TestCase
{
    use RefreshDatabase;
    use ModuleTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        
        \App\Models\Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);
        
        $this->cleanModuleDir();
    }

    protected function tearDown(): void
    {
        $this->cleanModuleDir();
        parent::tearDown();
    }

    public function test_module_can_be_upgraded_preserving_settings_and_updating_menus_permissions(): void
    {
        // 1. Scaffold v1.0.0
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->makeStrictCompliant();
        
        // Package v1.0.0
        Artisan::call('module:package', [
            'module' => $this->testAlias,
        ]);
        
        $zip1 = base_path("packages/{$this->testAlias}-1.0.0.zip");
        $this->assertFileExists($zip1);

        // 2. Prepare Version 1.0.1 in-place (keeping original migration file)
        $manifestPath = $this->getModulePath() . '/module.json';
        $manifest = json_decode(File::get($manifestPath), true);
        $manifest['version'] = '1.0.1';
        $manifest['settings'] = ['schema' => 'settings.json'];
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));

        // Add a new permission to permissions.json
        $permsPath = $this->getModulePath() . '/permissions.json';
        $permissions = json_decode(File::get($permsPath), true);
        $permissions[] = [
            'name' => 'mock_test_module_archive',
            'description' => 'Archive Mock Test entries',
        ];
        File::put($permsPath, json_encode($permissions, JSON_PRETTY_PRINT));

        // Package v1.0.1
        Artisan::call('module:package', [
            'module' => $this->testAlias,
        ]);

        $zip2 = base_path("packages/{$this->testAlias}-1.0.1.zip");
        $this->assertFileExists($zip2);

        // Clear local files to simulate install of v1.0.0 ZIP
        File::deleteDirectory($this->getModulePath());

        // Install & Activate v1.0.0
        $module = ModuleManager::install($zip1);
        $module = ModuleManager::activate($module->id);

        // Save a custom setting value
        $settingsService = app(ModuleSettingsService::class);
        $settingsService->save($module, [
            'hw_api_url' => 'https://custom-endpoint.com',
        ]);

        $this->assertEquals('https://custom-endpoint.com', $settingsService->getValues($module)['hw_api_url']);

        // 3. Trigger upgrade using v1.0.1 ZIP
        $upgradedModule = ModuleManager::install($zip2);
        
        // Assert version was updated
        $this->assertEquals('1.0.1', $upgradedModule->version);
        $this->assertEquals('active', $upgradedModule->status);

        // Assert custom settings value is preserved
        $this->assertEquals('https://custom-endpoint.com', $settingsService->getValues($upgradedModule)['hw_api_url']);

        // Assert new permission registered
        $this->assertDatabaseHas('permissions', [
            'name' => 'mock_test_module_archive',
        ]);
    }
}
