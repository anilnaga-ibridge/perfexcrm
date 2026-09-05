<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeRole(array $permissions, string $suffix = ''): Role
    {
        return Role::create([
            'name' => 'Test Role' . $suffix,
            'slug' => 'test-role' . $suffix . '-' . uniqid(),
            'permissions' => $permissions,
        ]);
    }

    protected function makeStaff(Role $role, array $overrides = [], string $suffix = ''): User
    {
        return User::create([
            'name' => 'Staff' . $suffix,
            'email' => 'staff' . $suffix . uniqid() . '@test.com',
            'password' => bcrypt('Secret123!'),
            'role_id' => $role->id,
            'role' => $role->slug,
            'active' => true,
            'permissions' => $overrides,
        ]);
    }

    public function test_admin_bypasses_all_permission_checks(): void
    {
        $adminRole = $this->makeRole([], '_admin');
        $admin = $this->makeStaff($adminRole);
        $admin->update(['role' => 'admin']);

        $this->assertTrue($admin->hasPermission('Settings.view'));
        $this->assertTrue($admin->hasPermission('Customers.create'));
    }

    public function test_staff_inherits_role_defaults(): void
    {
        $role = $this->makeRole([
            'Contracts' => ['view_global' => true, 'create' => true, 'delete' => false],
        ]);

        $staff = $this->makeStaff($role);

        $this->assertTrue($staff->hasPermission('Contracts.create'));
        $this->assertFalse($staff->hasPermission('Contracts.delete'));
        $this->assertFalse($staff->hasPermission('Invoices.create'));
    }

    public function test_settings_view_global_grants_settings_view(): void
    {
        // Settings feature only exposes view_global / edit caps.
        $role = $this->makeRole(['Settings' => ['view_global' => true]]);
        $staff = $this->makeStaff($role);

        $this->assertTrue($staff->fresh()->hasPermission('Settings.view'));

        // Grant via per-staff override on a role that lacks it.
        $role2 = $this->makeRole(['Settings' => ['view_global' => false, 'edit' => false]], '_2');
        $staff2 = $this->makeStaff($role2, ['Settings' => ['view_global' => true]], '_2');
        $this->assertTrue($staff2->fresh()->hasPermission('Settings.view'));
    }

    public function test_per_staff_grant_and_revoke_override_role(): void
    {
        $role = $this->makeRole([
            'Customers' => ['view_own' => true, 'view_global' => false, 'create' => false, 'edit' => false],
        ]);

        // Alice granted create via override.
        $alice = $this->makeStaff($role, ['Customers' => ['create' => true]], '_a');
        // Bob keeps role default (no create).
        $bob = $this->makeStaff($role, [], '_b');

        $this->assertTrue($alice->fresh()->hasPermission('Customers.create'));
        $this->assertFalse($bob->fresh()->hasPermission('Customers.create'));
    }

    public function test_saving_full_permissions_stores_only_overrides(): void
    {
        $role = $this->makeRole([
            'Contracts' => ['view_global' => true, 'create' => true, 'delete' => false],
        ]);

        $staff = $this->makeStaff($role);

        // UI sends the FULL effective set (exactly what the role grants).
        $fullSet = ['Contracts' => ['view_global' => true, 'create' => true, 'delete' => false]];

        $response = $this->actingAs($staff, 'sanctum')
            ->putJson("/api/staff/{$staff->id}", [
                'name' => $staff->name,
                'email' => $staff->email,
                'role_id' => $role->id,
                'permissions' => $fullSet,
            ]);

        $response->assertStatus(200);

        $raw = json_decode($staff->fresh()->getRawOriginal('permissions'), true);
        $this->assertSame([], $raw, 'Identical-to-role permissions must store zero overrides');
    }

    public function test_revoked_create_is_enforced_on_clients_api(): void
    {
        $role = $this->makeRole([
            'Customers' => ['view_own' => true, 'create' => true, 'edit' => true],
        ]);

        $staff = $this->makeStaff($role, ['Customers' => ['create' => false, 'edit' => false]]);

        $this->actingAs($staff, 'sanctum')
            ->postJson('/api/clients', ['company' => 'X'])
            ->assertStatus(403);

        $this->actingAs($staff, 'sanctum')
            ->putJson('/api/clients/1', ['company' => 'X'])
            ->assertStatus(403);
    }

    public function test_auth_user_endpoint_returns_effective_permissions(): void
    {
        $role = $this->makeRole([
            'Customers' => ['view_own' => true, 'create' => true],
        ]);

        $staff = $this->makeStaff($role, ['Customers' => ['create' => false]]);

        $response = $this->actingAs($staff, 'sanctum')->getJson('/api/auth/user');
        $response->assertStatus(200);

        $perms = $response->json('user.permissions');
        $this->assertFalse($perms['Customers']['create']);
        $this->assertTrue($perms['Customers']['view_own']);
    }

    public function test_role_edit_propagates_but_preserves_user_overrides(): void
    {
        $role = $this->makeRole([
            'Contracts' => ['view_global' => true, 'create' => true, 'edit' => false],
        ]);

        $withOverride = $this->makeStaff($role, ['Contracts' => ['delete' => true]], '_o');
        $withoutOverride = $this->makeStaff($role, [], '_n');

        // Edit the ROLE: add edit=true for everyone.
        $role->update([
            'permissions' => [
                'Contracts' => ['view_global' => true, 'create' => true, 'edit' => true],
            ],
        ]);

        $freshOverride = $withOverride->fresh();
        $freshPlain = $withoutOverride->fresh();

        // Both inherit the new edit grant.
        $this->assertTrue($freshOverride->hasPermission('Contracts.edit'));
        $this->assertTrue($freshPlain->hasPermission('Contracts.edit'));

        // The personal delete override survives the role edit.
        $this->assertTrue($freshOverride->hasPermission('Contracts.delete'));
        $this->assertFalse($freshPlain->hasPermission('Contracts.delete'));
    }

    public function test_legacy_string_list_permissions_are_normalized(): void
    {
        $role = $this->makeRole([]);
        $role->forceFill(['permissions' => json_encode(['leads' => ['view', 'create']])])->save();

        $staff = $this->makeStaff($role);

        $this->assertTrue($staff->fresh()->hasPermission('leads.view'));
        $this->assertTrue($staff->fresh()->hasPermission('leads.create'));
    }

    public function test_staff_without_any_matching_permission_is_denied_everywhere(): void
    {
        $role = $this->makeRole([]);
        $staff = $this->makeStaff($role);

        $this->assertFalse($staff->hasPermission('Customers.create'));
        $this->assertFalse($staff->hasPermission('Settings.view'));
        $this->assertFalse($staff->hasPermission('Invoices.delete'));

        // database-backups routes are guarded by permission:Settings.view middleware.
        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/database-backups')
            ->assertStatus(403);
    }

    public function test_settings_view_grants_access_to_settings_guarded_endpoints(): void
    {
        $role = $this->makeRole(['Settings' => ['view_global' => true]]);
        $staff = $this->makeStaff($role);

        $this->actingAs($staff, 'sanctum')
            ->getJson('/api/database-backups')
            ->assertStatus(200);
    }
}
