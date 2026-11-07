{{-- NID verification: status + upload form with image previews and client-side size/type checks. --}}
@extends('layouts.user')
@section('title', __('NID Verification'))

@php($canUpload = ! $document || in_array($document->status->value, ['rejected', 'resubmit']))

@section('content')
<x-page-header :title="__('NID Verification')" :subtitle="__('We need your National ID to verify your identity (KYC).')" />

@if($document)
    <div class="card card-pad mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-{{ $document->status->color() }}-100 text-{{ $document->status->color() }}-600 dark:bg-{{ $document->status->color() }}-500/15"><x-icon :name="$document->status->icon()" class="h-7 w-7" /></span>
        <div class="flex-1">
            <p class="text-sm text-slate-500">{{ __('Verification Status') }}</p>
            <x-status-badge :status="$document->status" size="lg" class="mt-1" />
            <p class="mt-2 text-sm text-slate-500">{{ __('NID') }}: <span class="font-mono">{{ $document->maskedNid() }}</span> · {{ __('Submitted') }}: {{ bn_digits($document->created_at->format('d M Y')) }}</p>
            @if($document->admin_note)<p class="mt-2 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800"><strong>{{ __('Note') }}:</strong> {{ $document->admin_note }}</p>@endif
        </div>
    </div>
@endif

@if($canUpload)
<form method="POST" action="{{ route('user.kyc.store') }}" enctype="multipart/form-data" class="space-y-6"
      x-data="{
          previews: {},
          error: {},
          pick(field, e) {
              const f = e.target.files[0];
              this.error[field] = null;
              if (!f) { this.previews[field] = null; return; }
              if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) { this.error[field] = @js(__('Only JPG, PNG or WEBP photos are allowed.')); e.target.value = ''; return; }
              if (f.size > 4 * 1024 * 1024) { this.error[field] = @js(__('Each photo must be smaller than 4 MB.')); e.target.value = ''; return; }
              this.previews[field] = URL.createObjectURL(f);
          }
      }">
    @csrf
    <div class="card card-pad">
        <x-input name="nid_number" :label="__('NID Number')" icon="id-card" inputmode="numeric" maxlength="20" required :hint="__('10, 13 or 17 digits, as printed on your NID.')" />
    </div>

    <div class="grid gap-5 sm:grid-cols-2 {{ $requireSelfie ? 'lg:grid-cols-3' : '' }}">
        @foreach(array_filter([
            'nid_front' => [__('NID front side'), 'id-card', true],
            'nid_back' => [__('NID back side'), 'credit-card', true],
            'selfie' => $requireSelfie ? [__('Selfie holding your NID'), 'camera', true] : null,
        ]) as $field => [$label, $icon, $required])
            <div class="card card-pad">
                <p class="label">{{ $label }} <span class="text-rose-500">*</span></p>
                <label class="group relative flex aspect-[16/10] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 text-center transition hover:border-primary hover:bg-primary/5 dark:border-slate-700 dark:bg-slate-800/50 @error($field) border-rose-400 @enderror">
                    <template x-if="previews.{{ $field }}"><img :src="previews.{{ $field }}" class="absolute inset-0 h-full w-full object-cover" alt=""></template>
                    <span x-show="!previews.{{ $field }}" class="flex flex-col items-center gap-2 p-4 text-slate-500">
                        <x-icon :name="$icon" class="h-9 w-9 text-slate-400 group-hover:text-primary" />
                        <span class="text-sm font-semibold">{{ __('Tap to take or choose a photo') }}</span>
                        <span class="text-xs">JPG, PNG, WEBP · {{ __('max 4 MB') }}</span>
                    </span>
                    <input type="file" name="{{ $field }}" accept="image/jpeg,image/png,image/webp" @if($field === 'selfie') capture="user" @endif class="sr-only" @change="pick('{{ $field }}', $event)" required>
                </label>
                <p class="error-text" x-show="error.{{ $field }}" x-text="error.{{ $field }}" x-cloak></p>
                @error($field)<p class="error-text">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>

    <div class="card card-pad space-y-4">
        <x-alert type="info" icon="lock">{{ __('Your photos are stored privately and encrypted. Only authorised staff can view them. They are never shown publicly.') }}</x-alert>
        <ul class="list-disc space-y-1 pl-5 text-sm text-slate-600 dark:text-slate-400">
            <li>{{ __('Take photos in good light. All 4 corners of the card must be visible.') }}</li>
            <li>{{ __('Text must be clear and readable. No blur or glare.') }}</li>
        </ul>
        <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="consent" value="1" class="checkbox mt-0.5" required> <span>{{ __('I allow :site to verify my NID for KYC purposes.', ['site' => site_name()]) }}</span></label>
        @error('consent')<p class="error-text">{{ $message }}</p>@enderror
        <button class="btn btn-primary btn-lg w-full sm:w-auto"><x-icon name="upload" />{{ __('Submit for Verification') }}</button>
    </div>
</form>
@elseif($document->status->value === 'approved')
    <div class="card"><x-empty icon="shield-check" :title="__('Your NID is verified')" :text="__('You can now apply for loans.')">
        <a href="{{ route('user.applications.create') }}" class="btn btn-primary"><x-icon name="file-pen-line" />{{ __('Apply Now') }}</a>
    </x-empty></div>
@endif
@endsection
