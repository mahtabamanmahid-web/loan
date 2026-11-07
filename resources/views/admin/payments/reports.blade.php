@extends('layouts.admin')
@section('title', __('Payment Reports'))

@section('content')
<x-page-header :title="__('Payment Reports')" :subtitle="$from->format('d M Y').' – '.$to->format('d M Y')" :back="route('admin.payments.index')">
    @can('payments.export')
        <a href="{{ route('admin.payments.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="btn btn-outline btn-sm" data-no-loading><x-icon name="file-spreadsheet" class="h-4 w-4" />{{ __('Export CSV / Excel') }}</a>
    @endcan
</x-page-header>

<form method="GET" class="card card-pad mb-6 flex flex-wrap items-end gap-3" data-no-loading>
    <div><label class="label" for="r-from">{{ __('From') }}</label><input id="r-from" type="date" name="from" value="{{ $from->toDateString() }}" class="input"></div>
    <div><label class="label" for="r-to">{{ __('To') }}</label><input id="r-to" type="date" name="to" value="{{ $to->toDateString() }}" class="input"></div>
    <button class="btn btn-primary">{{ __('Show') }}</button>
</form>

<div class="grid gap-6 lg:grid-cols-3">
    @foreach([
        [__('By status'), $byStatus, fn ($r) => \App\Enums\PaymentStatus::from($r->status)->label()],
        [__('Successful by purpose'), $byPurpose, fn ($r) => $r->purpose === 'installment' ? __('Loan Installment') : __('Processing Fee')],
        [__('Successful by method'), $byMethod, fn ($r) => $r->payment_method ?: '—'],
    ] as [$title, $rows, $label])
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ $title }}</h3>
            <table class="table"><tbody>
                @forelse($rows as $r)
                    <tr><td>{{ $label($r) }}</td><td class="text-right">{{ $r->total }}</td><td class="text-right font-semibold">{{ money_plain($r->amount) }}</td></tr>
                @empty
                    <tr><td class="text-center text-slate-500">{{ __('No data') }}</td></tr>
                @endforelse
            </tbody></table>
        </div>
    @endforeach
</div>

<div class="card mt-6 overflow-hidden">
    <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Daily successful payments') }}</h3>
    <div class="overflow-x-auto"><table class="table">
        <thead><tr><th>{{ __('Date') }}</th><th class="text-right">{{ __('Count') }}</th><th class="text-right">{{ __('Amount') }}</th></tr></thead>
        <tbody>
        @forelse($daily as $d)
            <tr><td>{{ \Illuminate\Support\Carbon::parse($d->day)->format('d M Y') }}</td><td class="text-right">{{ $d->total }}</td><td class="text-right font-semibold">{{ money_plain($d->amount) }}</td></tr>
        @empty
            <tr><td colspan="3" class="text-center text-slate-500">{{ __('No data') }}</td></tr>
        @endforelse
        </tbody>
        @if($daily->isNotEmpty())
            <tfoot><tr class="font-bold"><td class="px-4 py-3">{{ __('Total') }}</td><td class="px-4 py-3 text-right">{{ $daily->sum('total') }}</td><td class="px-4 py-3 text-right">{{ money_plain($daily->sum('amount')) }}</td></tr></tfoot>
        @endif
    </table></div>
</div>
@endsection
