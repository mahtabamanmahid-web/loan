<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Loan application validation. Only the loan amount/duration, the applicant's name,
 * occupation and loan reason are collected — nothing else.
 */
class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'applicant_name' => is_string($this->applicant_name) ? trim(strip_tags($this->applicant_name)) : $this->applicant_name,
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'loan_product_id' => ['required', 'integer', Rule::exists('loan_products', 'id')->where('is_active', true)],
            'loan_term_id' => ['required', 'integer', Rule::exists('loan_terms', 'id')->where('is_active', true)],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000000'],
            'applicant_name' => ['required', 'string', 'min:3', 'max:100'],
            'occupation' => ['required', Rule::in(array_keys(config('loan_options.occupations')))],
            'purpose' => ['required', Rule::in(array_keys(config('loan_options.purposes')))],
            'accept_terms' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'accept_terms.accepted' => __('You must accept the Terms & Conditions.'),
            'occupation.in' => __('Please select your occupation.'),
            'purpose.in' => __('Please select a loan reason.'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'applicant_name' => __('Applicant Name'),
            'occupation' => __('Occupation'),
            'purpose' => __('Loan Purpose'),
            'amount' => __('Loan Amount'),
        ];
    }
}
