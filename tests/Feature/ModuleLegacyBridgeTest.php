<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

class ModuleLegacyBridgeTest extends TestCase
{
    use RefreshDatabase;
    use ModuleTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);

        // Clean any old module files
        $this->cleanModuleDir();

        // Register the module in the database
        Module::create([
            'name' => 'Mock Test Module',
            'alias' => 'mock-test-module',
            'version' => '1.0.0',
            'status' => 'active',
        ]);

        // Create the directory structure for our mock module
        $modulePath = $this->getModulePath();
        File::ensureDirectoryExists($modulePath . '/controllers');
        File::ensureDirectoryExists($modulePath . '/views');

        // Create a mock controller file (using Whitelisted GET method name 'show')
        $controllerContent = '<?php
class Mock_test_controller extends AdminController {
    public function __construct() {
        parent::__construct();
    }

    public function manage_employees() {
        echo "<form action=\"/admin/mock_test_controller/save\" method=\"post\">
            <select class=\"selectpicker\"><option value=\"1\">Department 1</option></select>
            <input type=\"text\" class=\"datepicker\">
        </form>";
    }

    public function show($id = null) {
        return response()->json(["success" => true, "id" => intval($id)]);
    }
}';
        File::put($modulePath . '/controllers/Mock_test_controller.php', $controllerContent);
    }

    protected function tearDown(): void
    {
        $this->cleanModuleDir();
        parent::tearDown();
    }

    public function test_legacy_bridge_serves_pages_with_assets_and_csrf(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Access the legacy bridge view using clean hyphenated URL format (converted to underscored methodName)
        $response = $this->get('/plugins/mock-test-module/manage-employees');

        $response->assertStatus(200);

        // Verify the HTML includes our form and injected styles/scripts/tokens
        $response->assertSee('Manage Employees');
        $response->assertSee('<meta name="csrf-token"', false);
        $response->assertSee('bootstrap-select');
        $response->assertSee('bootstrap-datepicker');
        $response->assertSee('selectpicker');
        $response->assertSee('datepicker');
    }

    public function test_legacy_bridge_admin_controller_with_parameters(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Call the parameterized API route using clean hyphenated URL format (converted to underscored controller and methodName)
        $response = $this->get('/admin/mock-test-controller/show/42');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'id' => 42,
        ]);
    }
}
