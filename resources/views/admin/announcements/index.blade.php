@extends('layouts.admin')
@section('title', __('Announcements'))

@section('content')
<x-page-header :title="__('Announcements')" :subtitle="__('Banners shown at the top of the website or customer panel.')">
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('New') }}</a>
</x-page-header>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Audience') }}</th><th>{{ __('Period') }}</th><th>{{ __('Active') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($announcements as $a)
            <tr>
                <td class="font-semibold">{{ $a->title }}</td>
                <td><x-badge :color="['info' => 'sky', 'success' => 'emerald', 'warning' => 'amber', 'danger' => 'rose'][$a->type]">{{ $a->type }}</x-badge></td>
                <td>{{ $a->audience }}</td>
                <td class="text-xs">{{ $a->starts_at?->format('d M Y') ?? '…' }} – {{ $a->ends_at?->format('d M Y') ?? '…' }}</td>
                <td>{{ $a->is_active ? '✓' : '—' }}</td>
                <td class="text-right">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /></a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" data-confirm="{{ __('Delete?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty icon="megaphone" :title="__('No announcements')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $announcements->links() }}</div>
@endsection
