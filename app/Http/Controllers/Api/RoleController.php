<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Role::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        if ($request->user() && !$request->user()->hasPermission('Staff Roles.create')) {
            abort(403, 'Unauthorized. Missing required permission: Staff Roles.create');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $perms = isset($validated['permissions']) && is_array($validated['permissions'])
            ? \App\Models\User::normalizePermissionsArray($validated['permissions'])
            : null;

        $role = Role::create([
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'permissions' => $perms,
        ]);

        if (!empty($validated['permissions']) && is_array($validated['permissions'])) {
            $flatPerms = [];
            foreach ($validated['permissions'] as $key => $val) {
                if (is_string($val)) $flatPerms[] = $val;
                elseif (is_array($val)) {
                    foreach ($val as $k => $v) {
                        if ($v) $flatPerms[] = "{$key}.{$k}";
                    }
                }
            }
            $permissionIds = Permission::whereIn('name', $flatPerms)->pluck('id');
            $role->permissionRecords()->sync($permissionIds);
        }

        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Staff Roles.edit')) {
            abort(403, 'Unauthorized. Missing required permission: Staff Roles.edit');
        }

        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:roles,slug,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $roleData = [
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ];

        if (array_key_exists('permissions', $validated)) {
            $roleData['permissions'] = is_array($validated['permissions'])
                ? \App\Models\User::normalizePermissionsArray($validated['permissions'])
                : null;
        }

        $role->update($roleData);

        if (array_key_exists('permissions', $validated)) {
            if (!empty($validated['permissions']) && is_array($validated['permissions'])) {
                $flatPerms = [];
                foreach ($validated['permissions'] as $key => $val) {
                    if (is_string($val)) $flatPerms[] = $val;
                    elseif (is_array($val)) {
                        foreach ($val as $k => $v) {
                            if ($v) $flatPerms[] = "{$key}.{$k}";
                        }
                    }
                }
                $permissionIds = Permission::whereIn('name', $flatPerms)->pluck('id');
                $role->permissionRecords()->sync($permissionIds);
            } else {
                $role->permissionRecords()->detach();
            }
        }
        try {
            $tableName = Schema::hasTable('notifications') ? 'notifications' : (Schema::hasTable('tblnotifications') ? 'tblnotifications' : null);
            if ($tableName) {
                $adminUser = auth()->user();
                $adminName = $adminUser ? $adminUser->name : 'Administrator';
                $adminId = $adminUser ? $adminUser->id : 0;

                $details = [];
                if (isset($validated['permissions']) && is_array($validated['permissions'])) {
                    foreach ($validated['permissions'] as $feature => $actions) {
                        if (is_array($actions)) {
                            $enabled = [];
                            foreach ($actions as $act => $val) {
                                if ($val) {
                                    $enabled[] = ucwords(str_replace(['_', '-'], ' ', $act));
                                }
                            }
                            if (!empty($enabled)) {
                                $details[] = $feature . ' (' . implode(', ', $enabled) . ')';
                            }
                        }
                    }
                }

                $msgText = "Permissions for your role '{$role->name}' have been updated by {$adminName}.";
                if (!empty($details)) {
                    $msgText .= " Details: " . implode('; ', array_slice($details, 0, 5));
                }

                $affectedStaff = User::where('role_id', $role->id)->orWhere('role', $role->slug)->get();
                foreach ($affectedStaff as $staffMember) {
                    $notifData = [
                        'description' => $msgText,
                        'link'        => '/admin/dashboard',
                        'date'        => now(),
                    ];
                    if (Schema::hasColumn($tableName, 'touserid')) {
                        $notifData['touserid'] = $staffMember->id;
                    }
                    if (Schema::hasColumn($tableName, 'fromuserid')) {
                        $notifData['fromuserid'] = $adminId;
                    }
                    if (Schema::hasColumn($tableName, 'isread')) {
                        $notifData['isread'] = 0;
                    }
                    DB::table($tableName)->insert($notifData);
                }
            }
        } catch (\Throwable $e) {}

        return response()->json($role);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Staff Roles.delete')) {
            abort(403, 'Unauthorized. Missing required permission: Staff Roles.delete');
        }

        $role = Role::findOrFail($id);

        // Reassign users with this role to the default 'employee' role before deletion
        $defaultRole = Role::where('slug', 'employee')->first();
        if ($defaultRole) {
            \App\Models\User::where('role_id', $id)->update(['role_id' => $defaultRole->id, 'role' => $defaultRole->slug]);
        } else {
            \App\Models\User::where('role_id', $id)->update(['role_id' => null, 'role' => 'employee']);
        }

        $role->permissionRecords()->detach();
        $role->delete();
        return response()->json(['message' => 'Role deleted']);
    }
}
