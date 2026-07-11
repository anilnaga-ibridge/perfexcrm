<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Role;
use App\Services\ModuleSettingsService;
use App\Services\ModuleSettingValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ModuleSettingsTest extends TestCase
{
    use RefreshDatabase;

    private ModuleSettingsService $service;
    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);

        $this->service = app(ModuleSettingsService::class);

        // Create a test module with a settings.json
        $this->module = Module::create([
            'name' => 'Test Settings',
            'alias' => 'test-settings',
            'version' => '1.0.0',
            'status' => 'active',
            'author' => 'Test',
        ]);

        File::ensureDirectoryExists(base_path('Modules/test-settings'));
        File::put(base_path('Modules/test-settings/module.json'), json_encode([
            'name' => 'Test Settings',
            'alias' => 'test-settings',
            'version' => '1.0.0',
            'author' => 'Test',
            'description' => 'Test module for settings',
            'settings' => ['schema' => 'settings.json'],
        ]));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('Modules/test-settings'));
        File::deleteDirectory(base_path('Modules/no-settings-module'));
        parent::tearDown();
    }

    private function writeSettingsSchema(array $schema): void
    {
        File::put(base_path('Modules/test-settings/settings.json'), json_encode($schema));
        Cache::forget("module.schema.{$this->module->alias}");
    }

    private function createNoSettingsModule(): Module
    {
        $mod = Module::create([
            'name' => 'No Settings',
            'alias' => 'no-settings-module',
            'version' => '1.0.0',
            'status' => 'active',
            'author' => 'Test',
        ]);
        File::ensureDirectoryExists(base_path('Modules/no-settings-module'));
        File::put(base_path('Modules/no-settings-module/module.json'), json_encode([
            'name' => 'No Settings',
            'alias' => 'no-settings-module',
            'version' => '1.0.0',
            'author' => 'Test',
            'description' => 'No settings schema',
        ]));
        return $mod;
    }

    // ─── hasSettings() ───────────────────────────────────

    public function test_has_settings_returns_true_when_schema_defined(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'options' => ['USD', 'EUR']],
                ]],
            ],
        ]);
        $this->assertTrue($this->service->hasSettings($this->module));
    }

    public function test_has_settings_returns_false_without_schema_pointer(): void
    {
        $mod = $this->createNoSettingsModule();
        $this->assertFalse($this->service->hasSettings($mod));
    }

    // ─── getSchema() ─────────────────────────────────────

    public function test_get_schema_returns_parsed_settings(): void
    {
        $schema = [
            'schema_version' => 1,
            'sections' => [
                [
                    'title' => 'General',
                    'fields' => [
                        ['key' => 'currency', 'type' => 'select', 'options' => ['USD', 'EUR'], 'default' => 'USD'],
                    ],
                ],
            ],
        ];
        $this->writeSettingsSchema($schema);

        $result = $this->service->getSchema($this->module);
        $this->assertNotNull($result);
        $this->assertEquals(1, $result['schema_version']);
        $this->assertCount(1, $result['sections']);
        $this->assertEquals('currency', $result['sections'][0]['fields'][0]['key']);
    }

    public function test_get_schema_returns_null_when_no_settings(): void
    {
        $mod = $this->createNoSettingsModule();
        $this->assertNull($this->service->getSchema($mod));
    }

    public function test_get_schema_is_cached(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [],
        ]);

        // First call populates cache
        $this->service->getSchema($this->module);

        // Delete the file to prove cache is used
        File::delete(base_path('Modules/test-settings/settings.json'));

        $cached = $this->service->getSchema($this->module);
        $this->assertNotNull($cached);
        $this->assertEquals(1, $cached['schema_version']);
    }

    // ─── getValues() ─────────────────────────────────────

    public function test_get_values_returns_defaults_when_none_stored(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'default' => 'USD'],
                    ['key' => 'tax_rate', 'type' => 'number', 'default' => 18],
                ]],
            ],
        ]);

        $values = $this->service->getValues($this->module);
        $this->assertEquals('USD', $values['currency']);
        $this->assertEquals(18, $values['tax_rate']);
    }

    public function test_get_values_returns_persisted_values(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'default' => 'USD'],
                ]],
            ],
        ]);

        ModuleSetting::create([
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'EUR',
        ]);

        $values = $this->service->getValues($this->module);
        $this->assertEquals('EUR', $values['currency']);
    }

    // ─── getSettings() ────────────────────────────────

    public function test_get_settings_returns_schema_and_values(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'default' => 'USD'],
                ]],
            ],
        ]);

        $result = $this->service->getSettings($this->module);
        $this->assertNotNull($result);
        $this->assertArrayHasKey('schema', $result);
        $this->assertArrayHasKey('values', $result);
        $this->assertEquals('USD', $result['values']['currency']);
    }

    public function test_get_settings_returns_null_without_schema(): void
    {
        $mod = $this->createNoSettingsModule();
        $this->assertNull($this->service->getSettings($mod));
    }

    // ─── hasValue() ────────────────────────────────────

    public function test_has_value_returns_true_when_persisted(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'text'],
                ]],
            ],
        ]);

        ModuleSetting::create([
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'USD',
        ]);

        $this->assertTrue($this->service->hasValue($this->module, 'currency'));
    }

    public function test_has_value_returns_false_when_only_default_exists(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'text', 'default' => 'USD'],
                ]],
            ],
        ]);

        // No persisted row — only a schema default
        $this->assertFalse($this->service->hasValue($this->module, 'currency'));
    }

    public function test_has_value_returns_false_for_unknown_key(): void
    {
        $this->assertFalse($this->service->hasValue($this->module, 'nonexistent'));
    }

    // ─── save() ──────────────────────────────────────────

    public function test_save_persists_values(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'options' => ['USD', 'EUR', 'INR'], 'default' => 'USD'],
                    ['key' => 'tax_rate', 'type' => 'number', 'default' => 18],
                ]],
            ],
        ]);

        $result = $this->service->save($this->module, [
            'currency' => 'EUR',
            'tax_rate' => 15,
        ]);

        $this->assertEquals('EUR', $result['currency']);
        $this->assertEquals(15, $result['tax_rate']);

        $this->assertDatabaseHas('module_settings', [
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'EUR',
        ]);
    }

    public function test_save_updates_existing_keys(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'options' => ['USD', 'EUR'], 'default' => 'USD'],
                    ['key' => 'country', 'type' => 'text', 'default' => ''],
                ]],
            ],
        ]);

        // First save
        $this->service->save($this->module, ['currency' => 'USD', 'country' => 'India']);
        $this->assertEquals(2, ModuleSetting::where('module_id', $this->module->id)->count());

        // Partial save — only updates currency, leaves country untouched
        $this->service->save($this->module, ['currency' => 'EUR']);

        $this->assertDatabaseHas('module_settings', [
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'EUR',
        ]);
        $this->assertDatabaseHas('module_settings', [
            'module_id' => $this->module->id,
            'setting_key' => 'country',
            'setting_value' => 'India',
        ]);
    }

    public function test_save_throws_when_no_schema(): void
    {
        $mod = $this->createNoSettingsModule();
        $this->expectException(\RuntimeException::class);
        $this->service->save($mod, ['key' => 'value']);
    }

    public function test_save_validates_before_persist(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'tax_rate', 'type' => 'number', 'validation' => ['required' => true, 'min' => 0, 'max' => 100]],
                ]],
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->save($this->module, ['tax_rate' => 200]);
    }

    public function test_save_encrypts_password(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'API', 'fields' => [
                    ['key' => 'api_key', 'type' => 'password', 'default' => ''],
                ]],
            ],
        ]);

        $this->service->save($this->module, ['api_key' => 'secret-123']);

        $row = ModuleSetting::where('module_id', $this->module->id)
            ->where('setting_key', 'api_key')
            ->first();

        // Value should be encrypted (not plaintext)
        $this->assertNotNull($row);
        $this->assertNotEquals('secret-123', $row->setting_value);
        $this->assertStringStartsWith('eyJ', $row->setting_value); // base64-encoded encrypted payload
    }

    // ─── resetToDefaults() ───────────────────────────────

    public function test_reset_to_defaults_clears_persisted_values(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'currency', 'type' => 'select', 'options' => ['USD', 'EUR'], 'default' => 'USD'],
                    ['key' => 'tax_rate', 'type' => 'number', 'default' => 18],
                ]],
            ],
        ]);

        ModuleSetting::create(['module_id' => $this->module->id, 'setting_key' => 'currency', 'setting_value' => 'EUR']);
        ModuleSetting::create(['module_id' => $this->module->id, 'setting_key' => 'tax_rate', 'setting_value' => '25']);

        $result = $this->service->resetToDefaults($this->module);

        $this->assertDatabaseCount('module_settings', 0);
        $this->assertEquals('USD', $result['currency']);
        $this->assertEquals(18, $result['tax_rate']);
    }

    // ─── Validator ───────────────────────────────────────

    public function test_validator_required(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'name', 'type' => 'text', 'validation' => ['required' => true]],
            ]]],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('name is required');
        $validator->validate($schema, []);
    }

    public function test_validator_required_with_empty_string(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'name', 'type' => 'text', 'validation' => ['required' => true]],
            ]]],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['name' => '']);
    }

    public function test_validator_number_range(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'age', 'type' => 'number', 'validation' => ['min' => 0, 'max' => 150]],
            ]]],
        ];

        // Valid
        $result = $validator->validate($schema, ['age' => 25]);
        $this->assertEquals(25, $result['age']);

        // Below min
        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['age' => -1]);
    }

    public function test_validator_number_max(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'age', 'type' => 'number', 'validation' => ['min' => 0, 'max' => 150]],
            ]]],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at most');
        $validator->validate($schema, ['age' => 200]);
    }

    public function test_validator_select_options(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'color', 'type' => 'select', 'validation' => ['options' => ['red', 'blue', 'green']]],
            ]]],
        ];

        // Valid
        $result = $validator->validate($schema, ['color' => 'red']);
        $this->assertEquals('red', $result['color']);

        // Invalid
        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['color' => 'yellow']);
    }

    public function test_validator_multiselect_options(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'roles', 'type' => 'multiselect', 'validation' => ['options' => ['admin', 'user', 'guest']]],
            ]]],
        ];

        $result = $validator->validate($schema, ['roles' => ['admin', 'user']]);
        $this->assertEquals(['admin', 'user'], $result['roles']);

        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['roles' => ['admin', 'superadmin']]);
    }

    public function test_validator_email(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'email', 'type' => 'email'],
            ]]],
        ];

        $result = $validator->validate($schema, ['email' => 'user@example.com']);
        $this->assertEquals('user@example.com', $result['email']);

        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['email' => 'not-an-email']);
    }

    public function test_validator_url(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'site', 'type' => 'url'],
            ]]],
        ];

        $result = $validator->validate($schema, ['site' => 'https://example.com']);
        $this->assertEquals('https://example.com', $result['site']);

        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['site' => 'not-a-url']);
    }

    public function test_validator_pattern(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'code', 'type' => 'text', 'validation' => ['pattern' => '/^[A-Z]{3}$/']],
            ]]],
        ];

        $result = $validator->validate($schema, ['code' => 'ABC']);
        $this->assertEquals('ABC', $result['code']);

        $this->expectException(\InvalidArgumentException::class);
        $validator->validate($schema, ['code' => 'abcd']);
    }

    public function test_validator_boolean(): void
    {
        $validator = new ModuleSettingValidator();
        $schema = [
            'sections' => [['title' => 'G', 'fields' => [
                ['key' => 'enabled', 'type' => 'boolean'],
            ]]],
        ];

        $result = $validator->validate($schema, ['enabled' => true]);
        $this->assertTrue($result['enabled']);

        $result = $validator->validate($schema, ['enabled' => false]);
        $this->assertFalse($result['enabled']);

        $result = $validator->validate($schema, ['enabled' => '1']);
        $this->assertTrue($result['enabled']);

        $result = $validator->validate($schema, ['enabled' => '0']);
        $this->assertFalse($result['enabled']);
    }

    // ─── Database constraints ────────────────────────────

    public function test_database_unique_constraint(): void
    {
        ModuleSetting::create([
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'USD',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        ModuleSetting::create([
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'EUR',
        ]);
    }

    public function test_cascade_delete_on_module_removal(): void
    {
        ModuleSetting::create([
            'module_id' => $this->module->id,
            'setting_key' => 'currency',
            'setting_value' => 'USD',
        ]);

        $this->module->delete();

        $this->assertDatabaseCount('module_settings', 0);
    }

    public function test_save_creates_update_or_create(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 1,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'theme', 'type' => 'text', 'default' => 'light'],
                ]],
            ],
        ]);

        $this->service->save($this->module, ['theme' => 'dark']);
        $this->assertDatabaseCount('module_settings', 1);

        // Save again with same key — should update, not duplicate
        $this->service->save($this->module, ['theme' => 'light']);
        $this->assertDatabaseCount('module_settings', 1);
    }

    // ─── Schema versioning ────────────────────────────────

    public function test_schema_version_is_preserved(): void
    {
        $this->writeSettingsSchema([
            'schema_version' => 42,
            'sections' => [
                ['title' => 'General', 'fields' => [
                    ['key' => 'name', 'type' => 'text'],
                ]],
            ],
        ]);

        $schema = $this->service->getSchema($this->module);
        $this->assertEquals(42, $schema['schema_version']);
    }
}
