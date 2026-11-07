{{-- Empty state. Usage: <x-empty icon="inbox" title="No data" text="..."> <a ...>Action</a> </x-empty> --}}
@props(['icon' => 'inbox', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <span class="mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
        <x-icon :name="$icon" class="h-8 w-8" />
    </span>
    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $title }}</h3>
    @if($text)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>@endif
    @if(trim($slot))<div class="mt-5">{{ $slot }}</div>@endif
</div>
