{{-- Page title row. Usage: <x-page-header title="..." subtitle="..." back="url"> buttons </x-page-header> --}}
@props(['title', 'subtitle' => null, 'back' => null])
<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="flex items-center gap-3">
        @if($back)
            <a href="{{ $back }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-primary dark:border-slate-800 dark:bg-slate-900" aria-label="{{ __('Back') }}">
                <x-icon name="arrow-left" />
            </a>
        @endif
        <div>
            <h1 class="text-xl font-bold text-slate-900 sm:text-2xl dark:text-white">{{ $title }}</h1>
            @if($subtitle)<p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>@endif
        </div>
    </div>
    @if(trim($slot))<div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>@endif
</div>
