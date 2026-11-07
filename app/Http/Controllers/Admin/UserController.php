<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Customer management (staff accounts are managed in AdminUserController). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = User::whereNull('admin_role_id')->with('latestDocument')->withCount(['applications', 'loans']);

        if ($s = trim((string) $request->query('search'))) {
            $q->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }
        if ($request->query('filter') === 'blocked') {
            $q->where('is_blocked', true);
        } elseif (in_array($request->query('filter'), ['pending', 'approved', 'rejected', 'resubmit'], true)) {
            $q->whereHas('latestDocument', fn ($d) => $d->where('status', $request->query('filter')));
        } elseif ($request->query('filter') === 'no_kyc') {
            $q->doesntHave('documents');
        }

        return view('admin.users.index', ['users' => $q->latest()->paginate(20)->withQueryString()]);
    }

    public function show(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        return view('admin.users.show', [
            'user' => $user->load('profile', 'documents', 'applications.product', 'loans', 'tickets'),
            'transactions' => $user->transactions()->limit(20)->get(),
            'logins' => $user->loginHistories()->limit(10)->get(),
        ]);
    }

    public function edit(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        return view('admin.users.edit', ['user' => $user->load('profile')]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
        ]);

        $changes = array_keys(array_diff_assoc(collect($data)->only('name', 'phone', 'email')->all(), $user->only('name', 'phone', 'email')));
        $user->fill(collect($data)->only('name', 'phone', 'email')->all());
        if (! empty($data['password'])) {
            $user->password = $data['password'];
            $changes[] = 'password';
        }
        $user->save();

        ActivityLogger::log('user.updated', $user, "Customer {$user->phone} updated", ['fields' => $changes]);

        return redirect()->route('admin.users.show', $user)->with('success', __('Customer updated.'));
    }

    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);
        $data = $request->validate(['reason' => [$user->is_blocked ? 'nullable' : 'required', 'nullable', 'string', 'max:255']]);

        $user->forceFill([
            'is_blocked' => ! $user->is_blocked,
            'blocked_reason' => $user->is_blocked ? null : $data['reason'],
        ])->save();

        ActivityLogger::log($user->is_blocked ? 'user.blocked' : 'user.unblocked', $user, $data['reason'] ?? null);

        return back()->with('success', $user->is_blocked ? __('Customer blocked.') : __('Customer unblocked.'));
    }
}
