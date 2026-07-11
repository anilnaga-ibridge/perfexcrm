<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define baseline core permissions
        $corePermissions = [
            'core.users.view' => 'View Users',
            'core.users.create' => 'Create Users',
            'core.users.edit' => 'Edit Users',
            'core.users.delete' => 'Delete Users',
            'core.roles.view' => 'View Roles',
            'core.roles.manage' => 'Manage Roles',
            'core.settings.view' => 'View Settings',
            'core.settings.update' => 'Update Settings',
            'core.modules.view' => 'View Modules',
            'core.modules.manage' => 'Manage Modules',
        ];

        // 2. Insert permissions
        $permissionIds = [];
        foreach ($corePermissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
            $permissionIds[] = $permission->id;
        }

        // 3. Link permissions to Administrator role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            foreach ($permissionIds as $id) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $adminRole->id,
                    'permission_id' => $id,
                ]);
            }
        }

        // 4. Assign Admin Role to Administrator User (admin@test.com)
        $adminUser = User::where('email', 'admin@test.com')->first();
        if ($adminUser && $adminRole) {
            DB::table('user_roles')->insertOrIgnore([
                'user_id' => $adminUser->id,
                'role_id' => $adminRole->id,
            ]);
            
            // Sync role_id column for backward compatibility
            $adminUser->update(['role_id' => $adminRole->id]);
        }

        $this->command->info('Seeded baseline roles and permissions.');
    }
}
