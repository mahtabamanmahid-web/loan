<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('user.profile.edit', ['user' => $request->user()->load('profile', 'latestDocument')]);
    }

    /**
     * Name and mobile number are tied to the verified NID / OTP, so customers
     * cannot change them here (support can, after checking documents).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'present_address' => ['nullable', 'string', 'max:500'],
            'permanent_address' => ['nullable', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'monthly_income' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'date_of_birth' => ['nullable', 'date', 'before:-18 years'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'regex:/^01[3-9]\d{8}$/'],
        ], ['date_of_birth.before' => __('You must be at least 18 years old.')]);

        $user->update(['email' => $data['email'] ? strtolower($data['email']) : null]);
        $user->profile()->updateOrCreate([], collect($data)->except('email')->map(fn ($v) => is_string($v) ? strip_tags($v) : $v)->all());

        return back()->with('success', __('Profile updated.'));
    }

    public function avatar(Request $request): RedirectResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000']]);
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->update(['avatar_path' => $request->file('avatar')->store('avatars', 'public')]);

        return back()->with('success', __('Profile photo updated.'));
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ]);

        $request->user()->update(['password' => $request->password]);
        // Log out other devices that were using the old password
        auth()->logoutOtherDevices($request->password);

        return back()->with('success', __('Password changed successfully.'));
    }

    public function notifications(Request $request): RedirectResponse
    {
        $request->user()->update(['notification_preferences' => [
            'email' => $request->boolean('email'),
            'sms' => $request->boolean('sms'),
        ]]);

        return back()->with('success', __('Notification settings saved.'));
    }

    public function loginHistory(Request $request): View
    {
        return view('user.profile.logins', ['logins' => $request->user()->loginHistories()->paginate(20)]);
    }
}
