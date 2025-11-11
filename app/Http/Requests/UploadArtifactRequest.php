<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            'platform' => ['required', 'string', 'max:64'],
            'artifact' => ['required', 'file'],
            'filename' => ['nullable', 'string', 'max:255'],
            'signature' => ['nullable', 'string'],
            'sha256' => ['nullable', 'string'],
            'notarized' => ['sometimes', 'boolean'],
        ];
    }
}
