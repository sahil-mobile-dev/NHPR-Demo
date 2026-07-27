<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class VerifyMobileOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'txnId' => ['required', 'string'],
            'mobile' => ['required', 'string', 'digits:10'],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'txnId.required' => 'Transaction ID is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.digits' => 'Mobile number must be 10 digits.',
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must be 6 digits.',
        ];
    }
}
