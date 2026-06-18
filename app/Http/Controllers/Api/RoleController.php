<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:roles,slug,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update($validated);
        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'Role deleted']);
    }
}
