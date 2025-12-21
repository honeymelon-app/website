<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ActivateLicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'license_key' => ValidationRules::requiredString(),
            'app_version' => ValidationRules::requiredString(50),
            'device_id' => ValidationRules::optionalString(),
        ];
    }
}
