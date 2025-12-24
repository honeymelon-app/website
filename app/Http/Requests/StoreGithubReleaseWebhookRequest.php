<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGithubReleaseWebhookRequest extends FormRequest
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
            'notes' => ValidationRules::requiredString(),
            'published_at' => ['required', 'date'],
            'tag' => ValidationRules::requiredString(),
            'version' => ValidationRules::requiredString(),
            'channel' => ['required', 'string', Rule::in(['stable', 'beta', 'alpha', 'rc'])],
            'commit_hash' => ValidationRules::requiredString(),
            'major' => ['nullable', 'integer', 'min:0'],
            'artifacts' => ['nullable', 'array'],
            'artifacts.*.platform' => ['required_with:artifacts', ...ValidationRules::platform()],
            'artifacts.*.source' => ['nullable', 'string', Rule::in(['github', 'r2', 's3'])],
            'artifacts.*.filename' => ValidationRules::optionalString(),
            'artifacts.*.url' => ['required_with:artifacts', 'string', 'max:2048'],
            'artifacts.*.path' => ['nullable', 'string', 'max:2048'],
            'artifacts.*.size' => ['nullable', 'integer', 'min:0'],
            'artifacts.*.sha256' => ValidationRules::optionalString(),
            'artifacts.*.signature' => ValidationRules::optionalString(),
            'artifacts.*.notarized' => ['nullable', 'boolean'],
        ];
    }
}
