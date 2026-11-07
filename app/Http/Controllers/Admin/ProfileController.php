<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Lets any logged-in admin change their own name, email and password. */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['admin' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($admin->id)],
        ]);

        $admin->update($data);
        ActivityLogger::log('admin.profile_updated', $admin, 'Admin updated own profile');

        return back()->with('success', __('Profile updated.'));
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $request->user()->update(['password' => $request->password]);
        ActivityLogger::log('admin.password_changed', $request->user(), 'Admin changed own password');

        return back()->with('success', __('Password changed successfully.'));
    }
}
