@extends('layouts.admin')
@section('title', __('Reviews'))

@section('content')
<x-page-header :title="__('Customer Reviews')" :subtitle="__('Only verified reviews from real customers can be published.')">
    <a href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}" class="btn btn-outline btn-sm">{{ __('Unpublished') }}</a>
    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('Add Review') }}</a>
</x-page-header>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Verified') }}</th><th>{{ __('Published') }}</th><th>{{ __('Featured') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($reviews as $r)
            <tr>
                <td><p class="font-semibold">{{ $r->display_name }}</p><a href="{{ route('admin.users.show', $r->user) }}" class="text-xs text-primary">{{ $r->user->phone }}</a></td>
                <td class="text-amber-500">{{ str_repeat('★', $r->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $r->rating) }}</span></td>
                <td class="max-w-xs whitespace-normal text-xs">{{ \Illuminate\Support\Str::limit($r->comment, 120) }}</td>
                @foreach(['is_verified', 'is_published', 'is_featured'] as $f)
                    <td>
                        <form method="POST" action="{{ route('admin.reviews.toggle', [$r, $f]) }}">@csrf
                            <button class="inline-flex h-6 w-11 items-center rounded-full p-0.5 transition {{ $r->$f ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}" aria-label="{{ $f }}"><span class="h-5 w-5 rounded-full bg-white shadow transition {{ $r->$f ? 'translate-x-5' : '' }}"></span></button>
                        </form>
                    </td>
                @endforeach
                <td class="text-right">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.reviews.edit', $r) }}" class="btn btn-ghost btn-sm" aria-label="{{ __('Edit') }}"><x-icon name="pencil" class="h-4 w-4" /></a>
                        <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}" data-confirm="{{ __('Delete this review?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600" aria-label="{{ __('Delete') }}"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty icon="star" :title="__('No reviews yet')" :text="__('Customers with a loan can submit reviews from their dashboard.')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $reviews->links() }}</div>
@endsection
