@extends('layouts.admin')
@section('title', __('Review NID'))

@section('content')
<x-page-header :title="__('Review NID')" :subtitle="$document->user->name.' · '.$document->user->phone" :back="route('admin.kyc.index')">
    <x-status-badge :status="$document->status" size="lg" />
</x-page-header>


<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="grid gap-4 sm:grid-cols-2" x-data="{ zoom: null }">
            @foreach(array_filter(['front' => __('NID front side'), 'back' => __('NID back side'), 'selfie' => $document->selfie_path ? __('Selfie') : null]) as $side => $label)
                <figure class="card overflow-hidden">
                    <button type="button" @click="zoom = '{{ route('admin.kyc.file', [$document, $side]) }}'" class="block w-full bg-slate-100 dark:bg-slate-800">
                        <img src="{{ route('admin.kyc.file', [$document, $side]) }}" alt="{{ $label }}" class="h-56 w-full object-contain" loading="lazy">
                    </button>
                    <figcaption class="p-3 text-sm font-semibold">{{ $label }}</figcaption>
                </figure>
            @endforeach
            <div x-show="zoom" x-cloak @click="zoom = null" @keydown.escape.window="zoom = null" class="fixed inset-0 z-[80] grid place-items-center bg-black/85 p-4">
                <img :src="zoom" class="max-h-full max-w-full rounded-lg" alt="">
            </div>
        </div>
        <p class="text-xs text-slate-500"><x-icon name="lock" class="inline h-3.5 w-3.5" /> {{ __('Images are served from private storage and every view is logged.') }}</p>
    </div>

    <div class="space-y-6">
        <div class="card card-pad">
            <h3 class="mb-3 font-bold">{{ __('Details') }}</h3>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Name on account') }}</dt><dd class="font-semibold">{{ $document->user->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Date of Birth') }}</dt><dd>{{ $document->user->profile?->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Submitted') }}</dt><dd>{{ $document->created_at->format('d M Y, h:i A') }}</dd></div>
                @if($document->reviewer)<div><dt class="text-slate-500">{{ __('Reviewed by') }}</dt><dd>{{ $document->reviewer->name }} · {{ $document->reviewed_at?->format('d M Y') }}</dd></div>@endif
                @if($document->admin_note)<div><dt class="text-slate-500">{{ __('Note') }}</dt><dd>{{ $document->admin_note }}</dd></div>@endif
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.kyc.review', $document) }}" class="card card-pad space-y-4" x-data="{ status: 'approved' }">
            @csrf
            <h3 class="font-bold">{{ __('Decision') }}</h3>
            <div class="grid gap-2">
                @foreach(['approved' => ['Approve', 'emerald', 'shield-check'], 'resubmit' => ['Ask to upload again', 'orange', 'rotate-ccw'], 'rejected' => ['Reject', 'rose', 'shield-x']] as $v => [$l, $c, $i])
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 text-sm font-semibold transition" :class="status === '{{ $v }}' ? 'border-{{ $c }}-500 bg-{{ $c }}-50 dark:bg-{{ $c }}-500/10' : 'border-slate-200 dark:border-slate-700'">
                        <input type="radio" name="status" value="{{ $v }}" x-model="status" class="sr-only"><x-icon :name="$i" class="text-{{ $c }}-600" />{{ __($l) }}
                    </label>
                @endforeach
            </div>
            <x-textarea name="note" :label="__('Note for customer')" rows="3" x-bind:required="status !== 'approved'" :placeholder="__('Required when rejecting or asking to resubmit')" />
            <button class="btn btn-primary w-full"><x-icon name="check" />{{ __('Save Decision') }}</button>
        </form>
    </div>
</div>
@endsection
