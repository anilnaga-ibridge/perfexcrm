<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ModuleValidateTest extends TestCase
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

    public function test_generated_module_passes_strict_validation(): void
    {
        // 1. Generate
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);

        // 2. Make strict compliant (adds README, LICENSE, lang, test files, and sdk_version: 1.0)
        $this->makeStrictCompliant();

        // 3. Validate
        $exitCode = Artisan::call('module:validate', [
            'module' => $this->testAlias,
            '--strict' => true,
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode, "Validator failed output: " . $output);
        $this->assertMatchesRegularExpression('/Score:\s*100%/', $output);
        $this->assertMatchesRegularExpression('/Warnings:\s*0/', $output);
        $this->assertMatchesRegularExpression('/Errors:\s*0/', $output);
        $this->assertStringContainsString('Validation PASSED successfully!', $output);
    }
}
