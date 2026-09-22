<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $users = User::with('roles')->get();

        return view('admin.roles.index', compact('roles', 'permissions', 'users'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' created successfully.");
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', "Permissions for role '{$role->name}' updated successfully.");
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user->syncRoles($request->roles ?? []);

        return redirect()->route('roles.index')->with('success', "Roles for user '{$user->name}' updated successfully.");
    }
}
