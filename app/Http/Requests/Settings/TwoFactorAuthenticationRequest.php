<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorAuthenticationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Note: Two-factor authentication is now managed by Cerberus IAM.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ensure the state is valid (stub for compatibility).
     */
    public function ensureStateIsValid(): void
    {
        // No-op: 2FA state is managed by Cerberus IAM
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
