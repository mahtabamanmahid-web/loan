{{-- 6 separate boxes for the OTP code that fill a hidden "code" input. Supports paste & SMS autofill. --}}
<div x-data="{
        digits: ['', '', '', '', '', ''],
        get code() { return this.digits.join('') },
        input(i, e) {
            const v = e.target.value.replace(/\D/g, '');
            if (v.length > 1) { this.fill(v); return; }
            this.digits[i] = v;
            if (v && i < 5) this.$refs['d' + (i + 1)].focus();
        },
        back(i, e) { if (!this.digits[i] && i > 0) { this.$refs['d' + (i - 1)].focus(); } },
        fill(v) { v.slice(0, 6).split('').forEach((c, k) => this.digits[k] = c); this.$refs['d' + Math.min(v.length, 5)].focus(); },
     }" class="field">
    <label class="label">{{ __('Verification code') }}</label>
    <div class="flex justify-between gap-2" @paste.prevent="fill(($event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, ''))">
        @for($i = 0; $i < 6; $i++)
            <input x-ref="d{{ $i }}" type="text" inputmode="numeric" maxlength="6" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                   :value="digits[{{ $i }}]" @input="input({{ $i }}, $event)" @keydown.backspace="back({{ $i }}, $event)"
                   class="input h-14 w-full px-0 text-center text-2xl font-bold @error('code') input-error @enderror" aria-label="{{ __('Digit') }} {{ $i + 1 }}" @if($i === 0) autofocus @endif>
        @endfor
    </div>
    <input type="hidden" name="code" :value="code">
    @error('code')<p class="error-text"><x-icon name="circle-alert" class="h-4 w-4" />{{ $message }}</p>@enderror
</div>
