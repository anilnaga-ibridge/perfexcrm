<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Module;
use App\Services\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ModuleSdkTest extends TestCase
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

    public function test_complete_sdk_and_runtime_lifecycle(): void
    {
        $this->cleanModuleDir();

        // 1. Scaffold module
        $exit = Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->assertEquals(0, $exit);
        $this->assertDirectoryExists($this->getModulePath());

        // 2. Refine for strict compliance
        $this->makeStrictCompliant();

        // 3. Lint / Validate
        $exit = Artisan::call('module:validate', [
            'module' => $this->testAlias,
            '--strict' => true,
        ]);
        $this->assertEquals(0, $exit, Artisan::output());

        // 4. Package into ZIP
        $exit = Artisan::call('module:package', [
            'module' => $this->testAlias,
        ]);
        $this->assertEquals(0, $exit);

        $zipPath = base_path("packages/{$this->testAlias}-1.0.0.zip");
        $this->assertFileExists($zipPath);

        // Remove source files
        File::deleteDirectory($this->getModulePath());

        // 5. Install ZIP
        $module = ModuleManager::install($zipPath);
        $this->assertInstanceOf(Module::class, $module);
        $this->assertEquals('installed', $module->status);

        // 6. Activate
        $module = ModuleManager::activate($module->id);
        $this->assertEquals('active', $module->status);

        // 7. Deactivate
        $module = ModuleManager::deactivate($module->id);
        $this->assertEquals('inactive', $module->status);

        // 8. Uninstall
        ModuleManager::uninstall($module->id, true);
        $this->assertDirectoryDoesNotExist($this->getModulePath());
    }
}
