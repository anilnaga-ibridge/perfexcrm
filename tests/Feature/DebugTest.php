<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Module;
use App\Services\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/**
 * Tests for C1: dependent module reactivation after upgrade.
 * Calls ModuleManager::upgrade() directly to bypass HTTP routing issues.
 */
class DebugTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Administrator', 'slug' => 'admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function tearDown(): void
    {
        foreach (['p', 'c', 'g', 's'] as $alias) {
            $p = base_path("Modules/mod-{$alias}");
            if (is_dir($p)) {
                \Illuminate\Support\Facades\File::deleteDirectory($p);
            }
        }
        parent::tearDown();
    }

    private function createModuleZip(array $manifest): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'mod_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $m = array_merge([
            'name' => 'Test',
            'alias' => 'test',
            'version' => '1.0.0',
            'minimum_core_version' => '1.0.0',
            'description' => 'Test',
            'depends' => [],
        ], $manifest);
        $zip->addFromString('module.json', json_encode($m));
        $zip->addEmptyDir('Database/Migrations');
        $zip->close();
        return $zipPath;
    }

    public function test_single_dependent_reactivated(): void
    {
        // Install parent
        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent']);
        $parent = ModuleManager::install($zip);
        unlink($zip);
        ModuleManager::activate((string) $parent->id);
        $parent->refresh();
        $this->assertEquals('active', $parent->status);

        // Install child (depends on parent)
        $zip = $this->createModuleZip(['alias' => 'mod-c', 'name' => 'Child', 'depends' => ['mod-p']]);
        $child = ModuleManager::install($zip);
        unlink($zip);
        ModuleManager::activate((string) $child->id);
        $child->refresh();
        $this->assertEquals('active', $child->status);

        // Upgrade parent
        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent', 'version' => '2.0.0']);
        ModuleManager::install($zip);
        unlink($zip);

        // Verify child reactivated
        $child->refresh();
        $this->assertEquals('active', $child->status, 'Single dependent should be reactivated after parent upgrade');
    }

    public function test_multi_level_dependents_reactivated(): void
    {
        // Root → Middle → Leaf
        $zip = $this->createModuleZip(['alias' => 'mod-g', 'name' => 'Grandparent']);
        $gp = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $gp->id);

        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent', 'depends' => ['mod-g']]);
        $p = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $p->id);

        $zip = $this->createModuleZip(['alias' => 'mod-c', 'name' => 'Child', 'depends' => ['mod-p']]);
        $c = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $c->id);

        // Upgrade grandparent
        $zip = $this->createModuleZip(['alias' => 'mod-g', 'name' => 'Grandparent', 'version' => '2.0.0']);
        ModuleManager::install($zip); unlink($zip);

        $p->refresh();
        $c->refresh();
        $this->assertEquals('active', $p->status, 'Middle dependent should be reactivated');
        $this->assertEquals('active', $c->status, 'Leaf dependent should be reactivated');
    }

    public function test_sibling_dependents_reactivated(): void
    {
        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent']);
        $p = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $p->id);

        $zip = $this->createModuleZip(['alias' => 'mod-c', 'name' => 'SibA', 'depends' => ['mod-p']]);
        $a = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $a->id);

        $zip = $this->createModuleZip(['alias' => 'mod-s', 'name' => 'SibB', 'depends' => ['mod-p']]);
        $b = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $b->id);

        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent', 'version' => '2.0.0']);
        ModuleManager::install($zip); unlink($zip);

        $a->refresh();
        $b->refresh();
        $this->assertEquals('active', $a->status, 'Sibling A should be reactivated');
        $this->assertEquals('active', $b->status, 'Sibling B should be reactivated');
    }

    public function test_previously_inactive_dependent_not_reactivated(): void
    {
        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent']);
        $p = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $p->id);

        // Install child but keep inactive
        $zip = $this->createModuleZip(['alias' => 'mod-c', 'name' => 'Child', 'depends' => ['mod-p']]);
        $c = ModuleManager::install($zip); unlink($zip);
        // DO NOT ACTIVATE
        $c->refresh();
        $this->assertEquals('installed', $c->status);

        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Parent', 'version' => '2.0.0']);
        ModuleManager::install($zip); unlink($zip);

        $c->refresh();
        $this->assertEquals('installed', $c->status, 'Previously inactive dependent should stay inactive');
    }

    public function test_no_dependents(): void
    {
        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Solo']);
        $p = ModuleManager::install($zip); unlink($zip);
        ModuleManager::activate((string) $p->id);

        $zip = $this->createModuleZip(['alias' => 'mod-p', 'name' => 'Solo', 'version' => '2.0.0']);
        ModuleManager::install($zip); unlink($zip);

        $p->refresh();
        $this->assertEquals('active', $p->status, 'Standalone module should remain active');
    }
}
