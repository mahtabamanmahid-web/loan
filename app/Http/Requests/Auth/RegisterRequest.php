<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Normalise input before validation (e.g. +8801… → 01…, lower-case email). */
    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D/', '', (string) $this->input('phone'));
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }

        $this->merge([
            'phone' => $phone,
            'email' => strtolower(trim((string) $this->input('email'))),
            'name' => trim(strip_tags((string) $this->input('name'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            // Bangladeshi mobile: 01[3-9]XXXXXXXX. unique = duplicate account prevention
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/', 'unique:users,phone'],
            'email' => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid 11-digit mobile number (e.g. 01712345678).'),
            'phone.unique' => __('An account with this mobile number already exists. Please log in.'),
            'email.unique' => __('An account with this email already exists. Please log in.'),
            'terms.accepted' => __('You must accept the Terms & Conditions.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('Full Name'),
            'phone' => __('Mobile Number'),
            'email' => __('Email'),
            'password' => __('Password'),
        ];
    }
}
