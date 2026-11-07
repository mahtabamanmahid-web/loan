@extends('layouts.admin')
@section('title', $user->name)

@section('content')
<x-page-header :title="$user->name" :subtitle="$user->phone.($user->email ? ' · '.$user->email : '')" :back="route('admin.users.index')">
    @can('users.manage')
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm"><x-icon name="pencil" class="h-4 w-4" />{{ __('Edit') }}</a>
        @if($user->is_blocked)
            <form method="POST" action="{{ route('admin.users.block', $user) }}">@csrf<button class="btn btn-success btn-sm"><x-icon name="lock-open" class="h-4 w-4" />{{ __('Unblock') }}</button></form>
        @else
            <button type="button" class="btn btn-danger btn-sm" @click="$dispatch('open-modal', 'block')"><x-icon name="ban" class="h-4 w-4" />{{ __('Block') }}</button>
        @endif
    @endcan
</x-page-header>

@if($user->is_blocked)<x-alert type="danger" class="mb-5" :title="__('Blocked')">{{ $user->blocked_reason }}</x-alert>@endif

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6">
        <div class="card card-pad">
            <div class="flex items-center gap-4"><img src="{{ $user->avatarUrl() }}" class="h-16 w-16 rounded-2xl" alt=""><div><p class="text-lg font-bold">{{ $user->name }}</p><p class="text-sm text-slate-500">#{{ $user->id }} · {{ __('Joined') }} {{ $user->created_at->format('d M Y') }}</p></div></div>
            <dl class="mt-5 space-y-2 text-sm">
                @php($p = $user->profile)
                @foreach([
                    __('Mobile') => $user->phone,
                    __('Email') => $user->email,
                    __('Occupation') => $p?->occupation,
                    __('Monthly Income') => $p?->monthly_income ? money_plain($p->monthly_income) : null,
                    __('Date of Birth') => $p?->date_of_birth?->format('d M Y'),
                    __('Present Address') => $p?->present_address,
                    __('Permanent Address') => $p?->permanent_address,
                    __('Emergency Contact') => trim(($p?->emergency_contact_name).' '.($p?->emergency_contact_phone)),
                    __('Last login') => $user->last_login_at?->format('d M Y, h:i A').' · '.$user->last_login_ip,
                ] as $k => $v)
                    @if($v)<div><dt class="text-slate-500">{{ $k }}</dt><dd class="font-medium">{{ $v }}</dd></div>@endif
                @endforeach
            </dl>
        </div>

        <div class="card card-pad">
            <h3 class="mb-3 font-bold">{{ __('NID Verification') }}</h3>
            @forelse($user->documents as $d)
                <a href="{{ route('admin.kyc.show', $d) }}" class="mb-2 flex items-center justify-between rounded-xl border border-slate-100 p-3 text-sm hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                    <span>{{ __('NID photo') }} · {{ $d->created_at->format('d M Y') }}</span><x-status-badge :status="$d->status" />
                </a>
            @empty
                <p class="text-sm text-slate-500">{{ __('No NID submitted.') }}</p>
            @endforelse
        </div>

        <div class="card card-pad">
            <h3 class="mb-3 font-bold">{{ __('Recent Logins') }}</h3>
            @foreach($logins as $l)
                <p class="flex justify-between border-b border-slate-100 py-1.5 text-xs last:border-0 dark:border-slate-800"><span>{{ $l->created_at->format('d M, h:i A') }} · {{ $l->ip_address }}</span><span class="{{ $l->successful ? 'text-emerald-600' : 'text-rose-600' }}">{{ $l->successful ? __('Success') : __('Failed') }}</span></p>
            @endforeach
        </div>
    </div>

    <div class="space-y-6 lg:col-span-2">
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Loan History') }}</h3>
            <div class="overflow-x-auto"><table class="table">
                <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Package') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead>
                <tbody>
                @forelse($user->applications as $a)
                    <tr class="cursor-pointer" onclick="location.href='{{ route('admin.applications.show', $a) }}'"><td class="font-mono text-xs">{{ $a->reference }}</td><td>{{ $a->product->name }}</td><td>{{ money_plain($a->finalAmount()) }}</td><td><x-status-badge :status="$a->status" /></td><td>{{ $a->created_at->format('d M Y') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500">{{ __('No applications') }}</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @foreach($user->loans as $loan)
                <a href="{{ route('admin.loans.show', $loan) }}" class="flex items-center justify-between border-t border-slate-100 p-4 text-sm hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"><span>{{ __('Loan') }} {{ $loan->loan_number }} · {{ money_plain($loan->principal) }} · {{ __('Outstanding') }} {{ money_plain($loan->outstanding()) }}</span><x-status-badge :status="$loan->status" /></a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Transaction History') }}</h3>
            <div class="overflow-x-auto"><table class="table">
                <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Purpose') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead>
                <tbody>
                @forelse($transactions as $tx)
                    <tr class="cursor-pointer" onclick="location.href='{{ route('admin.payments.show', $tx) }}'"><td class="font-mono text-xs">{{ $tx->reference }}</td><td>{{ $tx->purposeLabel() }}</td><td>{{ money_plain($tx->amount) }}</td><td><x-status-badge :status="$tx->status" /></td><td>{{ $tx->created_at->format('d M Y') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500">{{ __('No transactions') }}</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<x-modal name="block" :title="__('Block customer')">
    <form method="POST" action="{{ route('admin.users.block', $user) }}" class="space-y-4">
        @csrf
        <x-textarea name="reason" :label="__('Reason')" rows="3" required />
        <button class="btn btn-danger w-full">{{ __('Block') }}</button>
    </form>
</x-modal>
@endsection
