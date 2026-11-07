<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => AdminRole::withCount(['users', 'permissions'])->get()]);
    }

    public function edit(AdminRole $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'groups' => AdminPermission::orderBy('id')->get()->groupBy('group'),
        ]);
    }

    public function update(Request $request, AdminRole $role): RedirectResponse
    {
        abort_if($role->slug === AdminRole::SUPER_ADMIN, 403, __('Super Admin always has every permission.'));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:admin_permissions,id'],
        ]);
        $role->update(collect($data)->only('name', 'description')->all());
        $role->permissions()->sync($data['permissions'] ?? []);
        ActivityLogger::log('role.updated', $role, "Role {$role->name} permissions updated", ['permissions' => $data['permissions'] ?? []]);

        return redirect()->route('admin.roles.index')->with('success', __('Role updated.'));
    }
}
