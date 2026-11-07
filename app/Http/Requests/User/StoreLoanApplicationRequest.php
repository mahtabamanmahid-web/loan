<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Server-side validation for the loan application wizard (all 4 steps). */
class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($v) => is_string($v) ? trim(strip_tags($v)) : $v;
        $phone = fn ($v) => $v ? preg_replace('/\D/', '', (string) $v) : $v;

        $this->merge([
            'applicant_name' => $clean($this->applicant_name),
            'present_address' => $clean($this->present_address),
            'permanent_address' => $clean($this->permanent_address),
            'occupation' => $clean($this->occupation),
            'purpose' => $clean($this->purpose),
            'emergency_contact_name' => $clean($this->emergency_contact_name),
            'emergency_contact_phone' => $phone($this->emergency_contact_phone),
        ]);
    }

    public function rules(): array
    {
        return [
            // Step 1 – amount & duration
            'loan_product_id' => ['required', 'integer', Rule::exists('loan_products', 'id')->where('is_active', true)],
            'loan_term_id' => ['required', 'integer', Rule::exists('loan_terms', 'id')->where('is_active', true)],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000000'],
            // Step 2 – personal information
            'applicant_name' => ['required', 'string', 'min:3', 'max:100'],
            'present_address' => ['required', 'string', 'min:5', 'max:500'],
            'permanent_address' => ['required', 'string', 'min:5', 'max:500'],
            'occupation' => ['required', 'string', 'max:100'],
            'monthly_income' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'purpose' => ['required', 'string', 'min:3', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'regex:/^01[3-9]\d{8}$/', 'not_in:'.$this->user()?->phone],
            // Step 4 – declarations
            'accept_terms' => ['accepted'],
            'accept_fees' => ['accepted'],
            'confirm_info' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'accept_terms.accepted' => __('You must accept the Terms & Conditions.'),
            'accept_fees.accepted' => __('Please confirm that you have read the fee disclosure.'),
            'confirm_info.accepted' => __('Please confirm that your information is correct.'),
            'emergency_contact_phone.regex' => __('Enter a valid 11-digit mobile number (e.g. 01712345678).'),
            'emergency_contact_phone.not_in' => __('Emergency contact must be a different person.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'applicant_name' => __('Applicant Name'),
            'present_address' => __('Present Address'),
            'permanent_address' => __('Permanent Address'),
            'occupation' => __('Occupation'),
            'monthly_income' => __('Monthly Income'),
            'purpose' => __('Loan Purpose'),
            'amount' => __('Loan Amount'),
        ];
    }
}
