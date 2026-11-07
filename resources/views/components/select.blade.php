{{-- Select box. Usage: <x-select name="category" :label="..." :options="['a' => 'A']" :selected="$x" /> --}}
@props(['name', 'label' => null, 'options' => [], 'selected' => null, 'placeholder' => null, 'hint' => null])
@php
    $id = $attributes->get('id', $name);
    $current = (string) old($name, $selected);
@endphp
<div class="field">
    @if($label)<label for="{{ $id }}" class="label">{{ $label }} @if($attributes->has('required'))<span class="text-rose-500">*</span>@endif</label>@endif
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->class(['input', 'input-error' => $errors->has($name)]) }}>
        @if($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $value => $text)
            <option value="{{ $value }}" @selected($current === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
    @if($hint && ! $errors->has($name))<p class="hint">{{ $hint }}</p>@endif
    @error($name)<p class="error-text"><x-icon name="circle-alert" class="h-4 w-4" />{{ $message }}</p>@enderror
</div>
