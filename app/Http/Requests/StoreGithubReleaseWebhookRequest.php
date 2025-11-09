<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            'notes' => ['required', 'string'],
            'published_at' => ['required', 'date'],
            'tag' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:255'],
            'channel' => ['required', 'string', Rule::in(['stable', 'beta', 'alpha', 'rc'])],
            'commit_hash' => ['required', 'string', 'max:255'],
            'major' => ['sometimes', 'boolean'],
            'artifacts' => ['sometimes', 'array'],
            'artifacts.*.platform' => ['required_with:artifacts', 'string', 'max:64'],
            'artifacts.*.source' => ['sometimes', 'string', Rule::in(['github', 'r2', 's3'])],
            'artifacts.*.filename' => ['sometimes', 'string', 'max:255'],
            'artifacts.*.url' => ['required_with:artifacts', 'string', 'max:2048'],
            'artifacts.*.path' => ['sometimes', 'string', 'max:2048'],
            'artifacts.*.size' => ['sometimes', 'integer', 'min:0'],
            'artifacts.*.sha256' => ['sometimes', 'string'],
            'artifacts.*.signature' => ['nullable', 'string'],
            'artifacts.*.notarized' => ['sometimes', 'boolean'],
        ];
    }
}
