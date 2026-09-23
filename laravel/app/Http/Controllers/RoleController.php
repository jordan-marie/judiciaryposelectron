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
        $roles = Role::withCount('users')->get();
        $users = User::with('roles')->get();
        return view('admin.roles.index', compact('roles', 'users'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        Role::create(['name' => $validated['name']]);

        return back()->with('success', "Role '{$validated['name']}' created successfully!");
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($validated['roles']);

        return back()->with('success', "Roles updated for user {$user->name}.");
    }
}
