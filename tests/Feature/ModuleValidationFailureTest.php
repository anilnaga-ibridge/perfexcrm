<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ModuleValidationFailureTest extends TestCase
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

    public function test_validation_fails_when_manifest_is_missing(): void
    {
        // Generate and delete manifest
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->makeStrictCompliant();
        
        File::delete($this->getModulePath() . '/module.json');

        $exitCode = Artisan::call('module:validate', [
            'module' => $this->testAlias,
            '--strict' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString("Missing 'module.json' manifest", Artisan::output());
    }

    public function test_validation_fails_with_duplicate_permissions(): void
    {
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->makeStrictCompliant();

        // Introduce duplicates in permissions.json
        $permsPath = $this->getModulePath() . '/permissions.json';
        $perms = [
            ['name' => 'test_duplicate_perm'],
            ['name' => 'test_duplicate_perm'],
        ];
        File::put($permsPath, json_encode($perms, JSON_PRETTY_PRINT));

        $exitCode = Artisan::call('module:validate', [
            'module' => $this->testAlias,
            '--strict' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString("Duplicate permission key/name detected", Artisan::output());
    }

    public function test_validation_fails_with_invalid_settings_field_type(): void
    {
        Artisan::call('module:make', [
            'name' => $this->testName,
            '--crud' => true,
            '--settings' => true,
        ]);
        $this->makeStrictCompliant();

        // Point module.json to the settings.json schema
        $manifestPath = $this->getModulePath() . '/module.json';
        $manifest = json_decode(File::get($manifestPath), true);
        $manifest['settings'] = ['schema' => 'settings.json'];
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));

        // Create a valid nested settings schema but with an unsupported type
        $invalidSchema = [
            'sections' => [
                [
                    'title' => 'General Settings',
                    'fields' => [
                        [
                            'key' => 'hw_api_url',
                            'type' => 'unsupported_field_type_x',
                            'label' => 'API Endpoint'
                        ]
                    ]
                ]
            ]
        ];
        $settingsPath = $this->getModulePath() . '/settings.json';
        File::put($settingsPath, json_encode($invalidSchema, JSON_PRETTY_PRINT));

        $exitCode = Artisan::call('module:validate', [
            'module' => $this->testAlias,
            '--strict' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString("contains unsupported type: 'unsupported_field_type_x'", Artisan::output());
    }
}
