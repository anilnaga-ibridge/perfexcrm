<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class ModuleMakeTest extends TestCase
{
    use ModuleTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanModuleDir();
    }

    protected function tearDown(): void
    {
        $this->cleanModuleDir();
        parent::tearDown();
    }

    public function test_make_generates_valid_module(): void
    {
        $exitCode = Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        $path = $this->getModulePath();
        $this->assertDirectoryExists($path);
        
        // Assert structure
        $this->assertFileExists($path . '/module.json');
        $this->assertFileExists($path . '/menu.json');
        $this->assertFileExists($path . '/permissions.json');
        $this->assertFileExists($path . '/settings.json');
        $this->assertFileExists($path . '/routes/api.php');
        $this->assertDirectoryExists($path . '/Http/Controllers');
        $this->assertDirectoryExists($path . '/resources/js/pages');

        // Check namespace prefix in controller template
        $controllerContent = File::get($path . '/Http/Controllers/Api/MockTestModuleController.php');
        $this->assertStringContainsString('namespace Modules\MockTestModule\Http\Controllers\Api;', $controllerContent);
    }
}
