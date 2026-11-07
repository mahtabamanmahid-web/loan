<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Staff accounts (Super Admin, Manager, Loan Officer, Payment Manager, Support Agent). */
class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index', ['admins' => User::whereNotNull('admin_role_id')->with('adminRole')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.admins.form', ['admin' => new User, 'roles' => AdminRole::orderBy('id')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $admin = User::create(collect($data)->only('name', 'email', 'phone', 'password')->all());
        $admin->forceFill(['admin_role_id' => $data['admin_role_id'], 'phone_verified_at' => now(), 'email_verified_at' => now()])->save();
        ActivityLogger::log('admin.created', $admin, "Admin {$admin->email} created");

        return redirect()->route('admin.admins.index')->with('success', __('Admin user saved.'));
    }

    public function edit(User $admin): View
    {
        abort_unless($admin->isAdmin(), 404);

        return view('admin.admins.form', ['admin' => $admin, 'roles' => AdminRole::orderBy('id')->get()]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);
        $data = $this->validated($request, $admin);

        if ($this->wouldRemoveLastSuperAdmin($admin, (int) $data['admin_role_id'])) {
            return back()->with('error', __('At least one Super Admin must remain.'));
        }

        $admin->fill(collect($data)->only('name', 'email', 'phone')->all());
        if (! empty($data['password'])) {
            $admin->password = $data['password'];
        }
        $admin->admin_role_id = $data['admin_role_id'];
        $admin->is_blocked = $request->boolean('is_blocked') && $admin->id !== auth()->id();
        $admin->save();
        ActivityLogger::log('admin.updated', $admin, "Admin {$admin->email} updated");

        return redirect()->route('admin.admins.index')->with('success', __('Admin user saved.'));
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);
        if ($admin->id === auth()->id()) {
            return back()->with('error', __('You cannot delete your own account.'));
        }
        if ($this->wouldRemoveLastSuperAdmin($admin, 0)) {
            return back()->with('error', __('At least one Super Admin must remain.'));
        }

        $admin->forceFill(['admin_role_id' => null, 'is_blocked' => true])->save();
        $admin->delete(); // soft delete keeps audit history intact
        ActivityLogger::log('admin.deleted', null, "Admin {$admin->email} removed");

        return back()->with('success', __('Admin user removed.'));
    }

    private function validated(Request $request, ?User $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($admin?->id)],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/', Rule::unique('users')->ignore($admin?->id)],
            'password' => [$admin ? 'nullable' : 'required', Password::min(10)->letters()->numbers()->mixedCase()],
            'admin_role_id' => ['required', 'exists:admin_roles,id'],
        ]);
    }

    private function wouldRemoveLastSuperAdmin(User $admin, int $newRoleId): bool
    {
        $superId = AdminRole::where('slug', AdminRole::SUPER_ADMIN)->value('id');

        return $admin->admin_role_id === $superId && $newRoleId !== $superId
            && User::where('admin_role_id', $superId)->count() <= 1;
    }
}
