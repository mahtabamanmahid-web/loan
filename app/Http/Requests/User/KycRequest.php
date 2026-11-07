<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * NID upload validation.
 * - NID number: 10, 13 or 17 digits (Bangladesh smart / old NID formats)
 * - Images: real image files only (MIME checked from file content), max 4 MB,
 *   minimum 300×200 px so the text is readable.
 */
class KycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nid_number' => preg_replace('/\D/', '', (string) $this->nid_number)]);
    }

    public function rules(): array
    {
        $image = ['file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096', 'dimensions:min_width=300,min_height=200'];

        return [
            'nid_number' => ['required', 'regex:/^(\d{10}|\d{13}|\d{17})$/'],
            'nid_front' => ['required', ...$image],
            'nid_back' => ['required', ...$image],
            'selfie' => [sys_setting('require_selfie') == '1' ? 'required' : 'nullable', ...$image],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nid_number.regex' => __('NID number must be 10, 13 or 17 digits.'),
            '*.max' => __('Each photo must be smaller than 4 MB.'),
            '*.mimes' => __('Only JPG, PNG or WEBP photos are allowed.'),
            '*.mimetypes' => __('Only JPG, PNG or WEBP photos are allowed.'),
            '*.dimensions' => __('The photo is too small. Please take a clearer photo.'),
            'consent.accepted' => __('Please allow us to verify your NID.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'nid_number' => __('NID Number'),
            'nid_front' => __('NID front photo'),
            'nid_back' => __('NID back photo'),
            'selfie' => __('Selfie'),
        ];
    }
}
