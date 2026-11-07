{{-- Active announcements for the given audience ("public" or "customers"). Dismissible per session. --}}
@php($announcements = \App\Models\Announcement::visibleTo($audience ?? 'public')->limit(3)->get())
@foreach($announcements as $a)
    @php($c = ['info' => 'sky', 'success' => 'emerald', 'warning' => 'amber', 'danger' => 'rose'][$a->type] ?? 'sky')
    <div x-data="{ show: (() => { try { return !sessionStorage.getItem('ann-{{ $a->id }}') } catch (e) { return true } })() }" x-show="show" x-cloak
         class="border-b border-{{ $c }}-200 bg-{{ $c }}-50 text-{{ $c }}-800 dark:border-{{ $c }}-500/20 dark:bg-{{ $c }}-500/10 dark:text-{{ $c }}-200">
        <div class="mx-auto flex max-w-7xl items-start gap-3 px-4 py-2.5 text-sm">
            <x-icon name="megaphone" class="mt-0.5 h-4 w-4" />
            <p class="flex-1"><strong>{{ $a->title }}:</strong> {{ $a->body }}</p>
            <button type="button" @click="show = false; try { sessionStorage.setItem('ann-{{ $a->id }}', 1) } catch (e) {}" aria-label="{{ __('Close') }}"><x-icon name="x" class="h-4 w-4" /></button>
        </div>
    </div>
@endforeach
