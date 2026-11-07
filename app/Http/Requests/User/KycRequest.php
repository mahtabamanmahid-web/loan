<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * NID upload validation — photos only, no NID number.
 * Images must be real image files (MIME checked from content), max 4 MB,
 * at least 300×200 px so the text is readable.
 */
class KycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $image = ['file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096', 'dimensions:min_width=300,min_height=200'];

        return [
            'nid_front' => ['required', ...$image],
            'nid_back' => ['required', ...$image],
            'selfie' => [sys_setting('require_selfie') == '1' ? 'required' : 'nullable', ...$image],
            'consent' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            '*.max' => __('Each photo must be smaller than 4 MB.'),
            '*.mimes' => __('Only JPG, PNG or WEBP photos are allowed.'),
            '*.mimetypes' => __('Only JPG, PNG or WEBP photos are allowed.'),
            '*.dimensions' => __('The photo is too small. Please take a clearer photo.'),
            'consent.accepted' => __('Please allow us to verify your NID.'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'nid_front' => __('NID front photo'),
            'nid_back' => __('NID back photo'),
            'selfie' => __('Selfie'),
        ];
    }
}
