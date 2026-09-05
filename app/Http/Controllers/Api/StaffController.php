<?php

namespace App\Http\Controllers\Api;

use App\Events\StaffCreated;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('role');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 25), 100);
        $staff = $query->orderBy('name')->paginate($perPage);

        $hasFullView = !$request->user() || $request->user()->hasPermission('Staff.view');

        $staff->getCollection()->transform(function ($user) use ($hasFullView) {
            $role = $user->relationLoaded('role') ? $user->getRelation('role') : null;
            $roleSlug = $role ? $role->slug : 'employee';
            
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleSlug,
                'profile_image' => $user->profile_image,
                'active' => $user->active,
                'department' => $user->department,
                'role_id' => $user->role_id,
                'role_data' => $role ? [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ] : null,
            ];

            if ($hasFullView) {
                $data['phone'] = $user->phone;
                $data['direction'] = $user->direction;
                $data['hourly_rate'] = $user->hourly_rate;
                $data['facebook'] = $user->facebook;
                $data['linkedin'] = $user->linkedin;
                $data['skype'] = $user->skype;
                $data['default_language'] = $user->default_language;
                $data['email_signature'] = $user->email_signature;
                $data['permissions'] = $user->permissions;
                $data['last_login'] = $user->last_login?->toISOString();
                $data['created_at'] = $user->created_at;
                $data['updated_at'] = $user->updated_at;
            }

            return $data;
        });

        return response()->json([
            'staff' => $staff,
            'total' => User::count(),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user() && !$request->user()->hasPermission('Staff.create')) {
            abort(403, 'Unauthorized. Missing required permission: Staff.create');
        }

        if (!$request->filled('name') && ($request->filled('first_name') || $request->filled('last_name'))) {
            $request->merge([
                'name' => trim($request->input('first_name', '') . ' ' . $request->input('last_name', ''))
            ]);
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email|max:255',
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'           => ['nullable', Rule::exists('roles', 'id')],
            'hourly_rate'       => 'nullable|numeric|min:0',
            'phone'             => 'nullable|string|max:50',
            'facebook'          => 'nullable|string|max:255',
            'linkedin'          => 'nullable|string|max:255',
            'skype'             => 'nullable|string|max:255',
            'default_language'  => 'nullable|string|max:10',
            'email_signature'   => 'nullable|string',
            'direction'         => 'nullable|string|max:10',
            'department'        => 'nullable|string|max:255',
            'active'            => 'boolean',
            'permissions'       => 'nullable|array',
        ]);

        $data = $validated;
        $data['password'] = Hash::make($validated['password']);

        if (!$request->filled('role_id')) {
            $employeeRole = Role::where('slug', 'employee')->first();
            if (!$employeeRole) {
                throw ValidationException::withMessages([
                    'role_id' => 'Default employee role not found.'
                ]);
            }
            $data['role_id'] = $employeeRole->id;
            $data['role'] = $employeeRole->slug;
        } else {
            $role = Role::find($data['role_id']);
            $data['role'] = $role ? $role->slug : 'employee';
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $normalized = User::normalizePermissionsArray($data['permissions']);
            $roleForDiff = Role::find($data['role_id']);
            $data['permissions'] = User::diffPermissionsAgainstRole($normalized, $roleForDiff);
        }

        $user = DB::transaction(function () use ($data) {
            return User::create($data);
        });

        $user->load('role');

        $creator = $request->user();
        DB::afterCommit(function () use ($user, $creator) {
            event(new StaffCreated($user, $creator));
        });
        $role = $user->relationLoaded('role') ? $user->getRelation('role') : null;

        $roleSlug = $role ? $role->slug : 'employee';
        $result = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleSlug,
            'profile_image' => $user->profile_image,
            'active' => $user->active,
            'phone' => $user->phone,
            'direction' => $user->direction,
            'department' => $user->department,
            'hourly_rate' => $user->hourly_rate,
            'facebook' => $user->facebook,
            'linkedin' => $user->linkedin,
            'skype' => $user->skype,
            'default_language' => $user->default_language,
            'email_signature' => $user->email_signature,
            'permissions' => $user->permissions,
            'last_login' => $user->last_login?->toISOString(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role_id' => $user->role_id,
            'role_data' => $role ? [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ] : null,
        ];

        return response()->json($result, 201);
    }

    public function show($id)
    {
        $user = User::with('role')->find($id);
        if (!$user) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        $role = $user->relationLoaded('role') ? $user->getRelation('role') : null;
        $roleSlug = $role ? $role->slug : 'employee';
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleSlug,
            'profile_image' => $user->profile_image,
            'active' => $user->active,
            'phone' => $user->phone,
            'direction' => $user->direction,
            'department' => $user->department,
            'hourly_rate' => $user->hourly_rate,
            'facebook' => $user->facebook,
            'linkedin' => $user->linkedin,
            'skype' => $user->skype,
            'default_language' => $user->default_language,
            'email_signature' => $user->email_signature,
            'permissions' => $user->permissions,
            'last_login' => $user->last_login?->toISOString(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role_id' => $user->role_id,
            'role_data' => $role ? [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ] : null,
        ];
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $user = User::with('role')->find($id);
        if (!$user) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        if (!$request->filled('name') && ($request->filled('first_name') || $request->filled('last_name'))) {
            $request->merge([
                'name' => trim($request->input('first_name', '') . ' ' . $request->input('last_name', ''))
            ]);
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email,' . $id,
            'password'          => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id'           => ['nullable', Rule::exists('roles', 'id')],
            'hourly_rate'       => 'nullable|numeric|min:0',
            'phone'             => 'nullable|string|max:50',
            'facebook'          => 'nullable|string|max:255',
            'linkedin'          => 'nullable|string|max:255',
            'skype'             => 'nullable|string|max:255',
            'default_language'  => 'nullable|string|max:10',
            'email_signature'   => 'nullable|string',
            'direction'         => 'nullable|string|max:10',
            'department'        => 'nullable|string|max:255',
            'active'            => 'boolean',
            'permissions'       => 'nullable|array',
        ]);

        $roleChanged = array_key_exists('role_id', $validated) && $validated['role_id'] != $user->role_id;
        $permsChanged = array_key_exists('permissions', $validated);

        if (!empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            $validated['role'] = $role ? $role->slug : 'employee';
        }

        if (isset($validated['permissions']) && is_array($validated['permissions'])) {
            $validated['permissions'] = User::normalizePermissionsArray($validated['permissions']);
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $oldUserPerms = $user->permissions;
        $user->update($validated);

        if ($roleChanged || $permsChanged) {
            try {
                $tableName = Schema::hasTable('notifications') ? 'notifications' : (Schema::hasTable('tblnotifications') ? 'tblnotifications' : null);
                if ($tableName) {
                    $adminUser = auth()->user();
                    $adminName = $adminUser ? $adminUser->name : 'Administrator';
                    $adminId = $adminUser ? $adminUser->id : 0;

                    $summaryParts = [];
                    if ($roleChanged) {
                        $newRoleObj = $user->role_id ? Role::find($user->role_id) : null;
                        $newRoleName = $newRoleObj ? $newRoleObj->name : ($user->role ?? 'Staff');
                        $summaryParts[] = "New Role: {$newRoleName}";
                    }

                    if ($permsChanged) {
                        $newUserPerms = $user->fresh()->permissions;
                        $grantedList = [];
                        $revokedList = [];

                        if (is_array($newUserPerms)) {
                            foreach ($newUserPerms as $feature => $newActions) {
                                $oldActions = $oldUserPerms[$feature] ?? [];
                                if (is_array($newActions)) {
                                    $grantedCaps = [];
                                    $revokedCaps = [];

                                    foreach ($newActions as $cap => $newVal) {
                                        $oldVal = is_array($oldActions) ? ($oldActions[$cap] ?? false) : false;
                                        $newBool = filter_var($newVal, FILTER_VALIDATE_BOOLEAN);
                                        $oldBool = filter_var($oldVal, FILTER_VALIDATE_BOOLEAN);

                                        if ($newBool && !$oldBool) {
                                            $grantedCaps[] = ucwords(str_replace(['_', '-'], ' ', $cap));
                                        } elseif (!$newBool && $oldBool) {
                                            $revokedCaps[] = ucwords(str_replace(['_', '-'], ' ', $cap));
                                        }
                                    }

                                    if (!empty($grantedCaps)) {
                                        $grantedList[] = $feature . ' (' . implode(', ', $grantedCaps) . ')';
                                    }
                                    if (!empty($revokedCaps)) {
                                        $revokedList[] = $feature . ' (' . implode(', ', $revokedCaps) . ')';
                                    }
                                }
                            }
                        }

                        if (!empty($grantedList)) {
                            $summaryParts[] = "Granted: " . implode('; ', array_slice($grantedList, 0, 4));
                        }
                        if (!empty($revokedList)) {
                            $summaryParts[] = "Revoked: " . implode('; ', array_slice($revokedList, 0, 4));
                        }
                    }

                    $msgText = "Your staff permissions have been updated by {$adminName}.";
                    if (!empty($summaryParts)) {
                        $msgText .= " Details: " . implode(' | ', $summaryParts);
                    }

                    $notifData = [
                        'description' => $msgText,
                        'link'        => '/admin/dashboard',
                        'date'        => now(),
                    ];
                    if (Schema::hasColumn($tableName, 'touserid')) {
                        $notifData['touserid'] = $user->id;
                    }
                    if (Schema::hasColumn($tableName, 'fromuserid')) {
                        $notifData['fromuserid'] = $adminId;
                    }
                    if (Schema::hasColumn($tableName, 'isread')) {
                        $notifData['isread'] = 0;
                    }
                    DB::table($tableName)->insert($notifData);
                }
            } catch (\Throwable $e) {}
        }

        $user->load('role');
        $role = $user->relationLoaded('role') ? $user->getRelation('role') : null;

        $roleSlug = $role ? $role->slug : 'employee';
        $result = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleSlug,
            'profile_image' => $user->profile_image,
            'active' => $user->active,
            'phone' => $user->phone,
            'direction' => $user->direction,
            'department' => $user->department,
            'hourly_rate' => $user->hourly_rate,
            'facebook' => $user->facebook,
            'linkedin' => $user->linkedin,
            'skype' => $user->skype,
            'default_language' => $user->default_language,
            'email_signature' => $user->email_signature,
            'permissions' => $user->permissions,
            'last_login' => $user->last_login?->toISOString(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role_id' => $user->role_id,
            'role_data' => $role ? [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ] : null,
        ];

        return response()->json($result);
    }

    public function uploadImage(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Staff not found'], 404);
        }

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $request->file('profile_image')->store('staff', 'public');
        $user->update(['profile_image' => 'storage/' . $path]);

        return response()->json(['profile_image' => $user->profile_image]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Staff.delete')) {
            abort(403, 'Unauthorized. Missing required permission: Staff.delete');
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'Staff member deleted successfully']);
    }

    public function roles()
    {
        return response()->json(Role::orderBy('name')->get());
    }
}
