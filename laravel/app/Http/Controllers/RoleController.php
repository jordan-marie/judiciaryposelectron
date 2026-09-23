<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Form;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['forms', 'users'])->withCount('users')->get();
        $forms = Form::where('is_active', true)->get();
        $users = User::with('roles')->get();
        return view('admin.roles.index', compact('roles', 'forms', 'users'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'forms' => 'nullable|array',
            'forms.*' => 'exists:forms,id',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['forms'])) {
            $role->forms()->sync($validated['forms']);
        }

        return back()->with('success', "Role '{$role->name}' created successfully!");
    }

    public function updateRoleForms(Request $request, Role $role)
    {
        $validated = $request->validate([
            'forms' => 'nullable|array',
            'forms.*' => 'exists:forms,id',
        ]);

        $role->forms()->sync($validated['forms'] ?? []);

        return back()->with('success', "Form permissions updated for role '{$role->name}'.");
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
