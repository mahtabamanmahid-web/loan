{{--
    Reusable search/filter bar (GET form).
    @include('admin.partials.filters', ['selects' => ['status' => ['' => 'All', ...]], 'dates' => true])
--}}
<form method="GET" class="card card-pad mb-5 flex flex-col gap-3 md:flex-row md:items-end" data-no-loading>
    <div class="flex-1">
        <label class="label" for="f-search">{{ __('Search') }}</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"><x-icon name="search" class="h-4 w-4" /></span>
            <input id="f-search" name="search" value="{{ request('search') }}" class="input pl-10" placeholder="{{ $placeholder ?? __('Name, phone, reference...') }}">
        </div>
    </div>
    @foreach($selects ?? [] as $name => $options)
        <div class="md:w-48">
            <label class="label" for="f-{{ $name }}">{{ __(ucfirst($name)) }}</label>
            <select id="f-{{ $name }}" name="{{ $name }}" class="input">
                @foreach($options as $v => $l)<option value="{{ $v }}" @selected((string) request($name) === (string) $v)>{{ $l }}</option>@endforeach
            </select>
        </div>
    @endforeach
    @if($dates ?? false)
        <div><label class="label" for="f-from">{{ __('From') }}</label><input id="f-from" type="date" name="from" value="{{ request('from') }}" class="input"></div>
        <div><label class="label" for="f-to">{{ __('To') }}</label><input id="f-to" type="date" name="to" value="{{ request('to') }}" class="input"></div>
    @endif
    <div class="flex gap-2">
        <button class="btn btn-primary"><x-icon name="filter" class="h-4 w-4" />{{ __('Filter') }}</button>
        <a href="{{ url()->current() }}" class="btn btn-outline" title="{{ __('Reset') }}"><x-icon name="rotate-ccw" class="h-4 w-4" /></a>
    </div>
</form>
