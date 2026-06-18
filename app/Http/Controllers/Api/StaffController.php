<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

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

        $perPage = $request->input('per_page', 25);
        $staff = $query->orderBy('name')->paginate($perPage);

        $staff->getCollection()->transform(function ($user) {
            $role = $user->relationLoaded('role') ? $user->getRelation('role') : null;
            $roleSlug = $role ? $role->slug : 'employee';
            return [
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
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'role_id' => $user->role_id,
                'role_data' => $role ? [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ] : null,
            ];
        });

        return response()->json([
            'staff' => $staff,
            'total' => User::count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email|max:255',
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'           => 'nullable|exists:roles,id',
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

        if (empty($data['role']) && !empty($data['role_id'])) {
            $role = Role::find($data['role_id']);
            $data['role'] = $role ? $role->slug : 'employee';
        } elseif (empty($data['role'])) {
            $data['role'] = 'employee';
        }

        $user = User::create($data);
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
            'last_login' => $user->last_login,
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
            'last_login' => $user->last_login,
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

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email,' . $id,
            'role_id'           => 'nullable|exists:roles,id',
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

        if (!empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            $validated['role'] = $role ? $role->slug : 'employee';
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);
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
            'last_login' => $user->last_login,
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

    public function destroy($id)
    {
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
