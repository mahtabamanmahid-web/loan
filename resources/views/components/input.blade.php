{{--
    Form field with label, icon, hint and validation error.
    Usage: <x-input name="phone" :label="__('Mobile Number')" icon="phone" required />
    Password fields automatically get a show / hide button.
--}}
@props(['name', 'label' => null, 'type' => 'text', 'icon' => null, 'hint' => null, 'value' => null])
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name));
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $hasError = $errors->has($dotName);
    $old = $type === 'password' ? null : old($dotName, $value);
@endphp
<div class="field" @if($type === 'password') x-data="{ show: false }" @endif>
    @if($label)
        <label for="{{ $id }}" class="label">{{ $label }} @if($attributes->has('required'))<span class="text-rose-500">*</span>@endif</label>
    @endif
    <div class="relative">
        @if($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
        <input id="{{ $id }}" name="{{ $name }}"
               @if($type === 'password') :type="show ? 'text' : 'password'" type="password" @else type="{{ $type }}" @endif
               @if($type !== 'file' && $type !== 'password') value="{{ $old }}" @endif
               {{ $attributes->except('id')->class(['input', 'pl-11' => $icon, 'pr-11' => $type === 'password', 'input-error' => $hasError]) }}
               @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
        @if($type === 'password')
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-primary" :aria-label="show ? @js(__('Hide password')) : @js(__('Show password'))">
                <span x-show="!show"><x-icon name="eye" /></span>
                <span x-show="show" x-cloak><x-icon name="eye-off" /></span>
            </button>
        @endif
    </div>
    @if($hint && ! $hasError)<p class="hint">{{ $hint }}</p>@endif
    @error($dotName)
        <p id="{{ $id }}-error" class="error-text"><x-icon name="circle-alert" class="h-4 w-4" />{{ $message }}</p>
    @enderror
</div>
