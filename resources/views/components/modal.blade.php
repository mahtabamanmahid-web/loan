{{--
    Modal dialog (Alpine).
    Open:  <button @click="$dispatch('open-modal', 'reject')">...</button>
    Use:   <x-modal name="reject" :title="__('Reject')"> ...form... </x-modal>
--}}
@props(['name', 'title' => null, 'maxWidth' => 'max-w-lg'])
<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') { open = true; $nextTick(() => window.renderIcons && renderIcons()) }"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0 translate-y-8"
         class="relative max-h-[92vh] w-full {{ $maxWidth }} overflow-y-auto rounded-t-3xl bg-white p-6 shadow-2xl sm:rounded-3xl dark:bg-slate-900">
        <div class="mb-4 flex items-center justify-between gap-4">
            @if($title)<h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>@endif
            <button type="button" @click="open = false" class="btn-ghost -mr-2 rounded-lg p-2" aria-label="{{ __('Close') }}"><x-icon name="x" /></button>
        </div>
        {{ $slot }}
    </div>
</div>
