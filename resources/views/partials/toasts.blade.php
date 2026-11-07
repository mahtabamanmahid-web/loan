{{--
    Toast notification stack (top-right on desktop, top on mobile).
    Session flashes  ->with('success'|'error'|'warning'|'info', '...')  appear automatically.
    From JavaScript: toast('success', 'Saved!')
--}}
@php
    $flashes = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($t) => session()->has($t))
        ->map(fn ($t) => ['type' => $t, 'message' => session($t)])
        ->values();
    if ($errors->any() && ! session()->has('error')) {
        $flashes->push(['type' => 'error', 'message' => __('Please correct the highlighted fields.')]);
    }
@endphp
<div x-data x-init="@js($flashes).forEach(f => $store.toasts.add(f.type, f.message, 6000))"
     class="pointer-events-none fixed inset-x-0 top-3 z-[70] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-4 sm:items-end" aria-live="polite">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-3 sm:translate-x-6 sm:translate-y-0" x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 scale-95"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border bg-white p-4 shadow-2xl shadow-slate-900/10 dark:bg-slate-900"
             :class="{
                'border-emerald-200 dark:border-emerald-500/30': t.type === 'success',
                'border-rose-200 dark:border-rose-500/30': t.type === 'error',
                'border-amber-200 dark:border-amber-500/30': t.type === 'warning',
                'border-sky-200 dark:border-sky-500/30': t.type === 'info' }">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl animate-pop"
                  :class="{
                    'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15': t.type === 'success',
                    'bg-rose-100 text-rose-600 dark:bg-rose-500/15': t.type === 'error',
                    'bg-amber-100 text-amber-600 dark:bg-amber-500/15': t.type === 'warning',
                    'bg-sky-100 text-sky-600 dark:bg-sky-500/15': t.type === 'info' }">
                <template x-if="t.type === 'success'"><i data-lucide="circle-check" class="h-5 w-5"></i></template>
                <template x-if="t.type === 'error'"><i data-lucide="circle-x" class="h-5 w-5"></i></template>
                <template x-if="t.type === 'warning'"><i data-lucide="triangle-alert" class="h-5 w-5"></i></template>
                <template x-if="t.type === 'info'"><i data-lucide="info" class="h-5 w-5"></i></template>
            </span>
            <p class="flex-1 pt-1.5 text-sm font-medium text-slate-700 dark:text-slate-200" x-text="t.message"></p>
            <button type="button" @click="$store.toasts.remove(t.id)" class="rounded-lg p-1 text-slate-400 hover:text-slate-600" aria-label="{{ __('Close') }}">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>
    </template>
</div>
