{{-- Multi-line text field. Usage: <x-textarea name="message" :label="..." rows="4" /> --}}
@props(['name', 'label' => null, 'value' => null, 'hint' => null])
@php($id = $attributes->get('id', $name))
<div class="field">
    @if($label)<label for="{{ $id }}" class="label">{{ $label }} @if($attributes->has('required'))<span class="text-rose-500">*</span>@endif</label>@endif
    <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->merge(['rows' => 4])->class(['input', 'input-error' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @if($hint && ! $errors->has($name))<p class="hint">{{ $hint }}</p>@endif
    @error($name)<p class="error-text"><x-icon name="circle-alert" class="h-4 w-4" />{{ $message }}</p>@enderror
</div>
