<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UploadArtifactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => ValidationRules::platform(),
            'artifact' => ['required', 'file'],
            'filename' => ValidationRules::optionalString(),
            'signature' => ValidationRules::optionalString(),
            'sha256' => ValidationRules::optionalString(),
            'notarized' => ['sometimes', 'boolean'],
        ];
    }
}
