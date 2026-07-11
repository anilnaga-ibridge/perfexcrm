<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;

trait ModuleTestHelper
{
    protected string $testName = 'Mock Test Module';
    protected string $testAlias = 'mock-test-module';

    /**
     * Get the target test module path.
     */
    protected function getModulePath(): string
    {
        return base_path('Modules/' . $this->testAlias);
    }

    /**
     * Clean up any generated test module directories and packaged ZIP files.
     */
    protected function cleanModuleDir(): void
    {
        $path = $this->getModulePath();
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }

        $packagesDir = base_path('packages');
        if (File::isDirectory($packagesDir)) {
            $zips = glob($packagesDir . '/' . $this->testAlias . '-*.zip');
            foreach ($zips as $zip) {
                File::delete($zip);
            }
        }
    }

    /**
     * Touch dummy files and parameters to satisfy 100% strict compliance rules.
     */
    protected function makeStrictCompliant(): void
    {
        $path = $this->getModulePath();
        File::ensureDirectoryExists($path);
        File::ensureDirectoryExists($path . '/Models');
        File::ensureDirectoryExists($path . '/Database/Migrations');
        File::ensureDirectoryExists($path . '/tests/Feature');
        File::ensureDirectoryExists($path . '/lang/en');

        File::put($path . '/README.md', '# Mock Test Module');
        File::put($path . '/LICENSE', 'MIT License');
        File::put($path . '/lang/en/messages.php', '<?php return [];');
        File::put($path . '/tests/Feature/ExampleTest.php', '<?php
namespace Modules\MockTestModule\Tests\Feature;
use Tests\TestCase;
class ExampleTest extends TestCase {
    public function test_example() { $this->assertTrue(true); }
}');

        $manifestPath = $path . '/module.json';
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            $manifest['sdk_version'] = '1.0';
            $manifest['settings'] = ['schema' => 'settings.json'];
            File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT));
        }

        // Write a strictly compliant settings.json
        $settingsPath = $path . '/settings.json';
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
    }
}
