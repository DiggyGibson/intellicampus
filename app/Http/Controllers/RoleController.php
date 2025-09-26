<?php
// File: C:\IntelliCampus\development\backend\app\Http\Controllers\RoleController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        return response()->json($roles);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles',
            'slug' => 'required|string|max:50|unique:roles',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create($validated);
        return response()->json(['message' => 'Role created successfully', 'role' => $role]);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['users', 'permissions']);
        return response()->json($role);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            return response()->json(['message' => 'Cannot modify system role'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $role->update($validated);
        return response()->json(['message' => 'Role updated successfully', 'role' => $role]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return response()->json(['message' => 'Cannot delete system role'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 400);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }
}