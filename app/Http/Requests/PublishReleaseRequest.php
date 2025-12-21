<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\SemanticVersion;
use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class PublishReleaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Add authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tag' => ['required', 'string', new SemanticVersion(allowVPrefix: true)],
            'channel' => ValidationRules::releaseChannel(),
        ];
    }
}
