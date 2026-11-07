@extends('layouts.admin')
@section('title', __('Backups'))

@section('content')
<x-page-header :title="__('Database Backup Management')" :subtitle="__('Backups are stored privately on the server. Download and keep a copy off-site.')">
    <form method="POST" action="{{ route('admin.backups.store') }}">@csrf<button class="btn btn-primary btn-sm"><x-icon name="database-backup" class="h-4 w-4" />{{ __('Create Backup Now') }}</button></form>
</x-page-header>

<x-alert type="info" class="mb-5">{{ __('Backups contain personal data. Store downloaded files securely and delete old ones. Uploaded NID images are in storage/app/private/kyc and must be backed up separately (see README).') }}</x-alert>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('File') }}</th><th>{{ __('Size') }}</th><th>{{ __('Created') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($backups as $b)
            <tr>
                <td class="font-mono text-xs">{{ $b['name'] }}</td>
                <td>{{ number_format($b['size'] / 1024, 1) }} KB</td>
                <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($b['date'])->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</td>
                <td class="text-right">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.backups.download', $b['name']) }}" class="btn btn-outline btn-sm" data-no-loading><x-icon name="download" class="h-4 w-4" />{{ __('Download') }}</a>
                        <form method="POST" action="{{ route('admin.backups.destroy', $b['name']) }}" data-confirm="{{ __('Delete this backup?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><x-empty icon="database" :title="__('No backups yet')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
