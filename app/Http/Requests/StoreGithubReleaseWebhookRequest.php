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
            'notes' => ValidationRules::optionalString(65535),
            'published_at' => ['required', 'date'],
            'tag' => ValidationRules::requiredString(),
            'version' => ValidationRules::requiredString(),
            'name' => ValidationRules::optionalString(),
            'channel' => ValidationRules::releaseChannel(),
            'commit_hash' => ValidationRules::requiredString(),
            'author' => ValidationRules::optionalString(),
            'html_url' => ValidationRules::optionalString(2048),
            'target_commitish' => ValidationRules::optionalString(),
            'github_id' => ['nullable', 'integer', 'min:0'],
            'prerelease' => ['nullable', 'boolean'],
            'draft' => ['nullable', 'boolean'],
            'github_created_at' => ['nullable', 'date'],
            'major' => ['nullable', 'integer', 'min:0'],
            'artifacts' => ['nullable', 'array'],
            'artifacts.*.platform' => ['required_with:artifacts', ...ValidationRules::platform()],
            'artifacts.*.source' => ['nullable', 'string', Rule::in(['github', 'r2', 's3'])],
            'artifacts.*.state' => ValidationRules::optionalString(),
            'artifacts.*.filename' => ValidationRules::optionalString(),
            'artifacts.*.content_type' => ValidationRules::optionalString(),
            'artifacts.*.url' => ['required_with:artifacts', 'string', 'max:2048'],
            'artifacts.*.path' => ValidationRules::optionalString(2048),
            'artifacts.*.size' => ['nullable', 'integer', 'min:0'],
            'artifacts.*.download_count' => ['nullable', 'integer', 'min:0'],
            'artifacts.*.sha256' => ValidationRules::optionalString(),
            'artifacts.*.signature' => ValidationRules::optionalString(512),
            'artifacts.*.notarized' => ['nullable', 'boolean'],
            'artifacts.*.github_id' => ['nullable', 'integer', 'min:0'],
            'artifacts.*.github_created_at' => ['nullable', 'date'],
            'artifacts.*.github_updated_at' => ['nullable', 'date'],
        ];
    }
}
