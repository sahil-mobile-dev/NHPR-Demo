<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SendAadhaarOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aadhaar' => ['required', 'string', 'digits:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'aadhaar.required' => 'Aadhaar number is required.',
            'aadhaar.digits' => 'Aadhaar number must be exactly 12 digits.',
        ];
    }
}
