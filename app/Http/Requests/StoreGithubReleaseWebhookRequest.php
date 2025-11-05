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
            'channel' => ['required', 'string', Rule::in(['stable', 'beta'])],
            'commit_hash' => ['required', 'string', 'max:255'],
            'major' => ['sometimes', 'boolean'],
        ];
    }
}
