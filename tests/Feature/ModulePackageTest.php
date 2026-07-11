<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ModulePackageTest extends TestCase
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

    public function test_module_can_be_packaged(): void
    {
        // 1. Generate & Refine
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->makeStrictCompliant();

        // Add a fake forbidden file and folder to test exclusions
        File::put($this->getModulePath() . '/.env', 'SECRET_KEY=123456');
        File::ensureDirectoryExists($this->getModulePath() . '/node_modules');
        File::put($this->getModulePath() . '/node_modules/dummy.txt', 'forbidden');

        // 2. Package
        $exitCode = Artisan::call('module:package', [
            'module' => $this->testAlias,
        ]);

        $this->assertEquals(0, $exitCode, Artisan::output());

        $zipPath = base_path("packages/{$this->testAlias}-1.0.0.zip");
        $this->assertFileExists($zipPath);
        $this->assertGreaterThan(0, filesize($zipPath));

        // 3. Inspect ZIP contents
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath));

        // Assert manifest is inside under prefix folder
        $this->assertNotFalse($zip->locateName("{$this->testAlias}/module.json"));

        // Assert forbidden files are excluded
        $this->assertFalse($zip->locateName("{$this->testAlias}/.env"));
        $this->assertFalse($zip->locateName("{$this->testAlias}/node_modules/dummy.txt"));

        $zip->close();
    }
}
