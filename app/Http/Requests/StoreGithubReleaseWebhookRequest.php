<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ReleaseChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGithubReleaseWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verify GitHub webhook signature if secret is configured
        $secret = config('services.github.webhook_secret');

        if (empty($secret)) {
            // If no secret is configured, fall back to Sanctum authentication
            return $this->user() !== null;
        }

        return $this->verifyGithubSignature($secret);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tag' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:255'],
            'channel' => ['required', 'string', Rule::in(['stable', 'beta'])],
            'commit_hash' => ['required', 'string', 'max:255'],
            'is_major' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tag' => 'release tag',
            'commit_hash' => 'commit hash',
            'is_major' => 'major release flag',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'channel.in' => 'The channel must be either "stable" or "beta".',
            'tag.required' => 'A release tag is required.',
            'version.required' => 'A version number is required.',
            'commit_hash.required' => 'A commit hash is required.',
        ];
    }

    /**
     * Get the validated channel as an enum.
     */
    public function getChannel(): ReleaseChannel
    {
        return ReleaseChannel::from($this->validated('channel'));
    }

    /**
     * Verify GitHub webhook signature.
     */
    protected function verifyGithubSignature(string $secret): bool
    {
        $signature = $this->header('X-Hub-Signature-256');

        if (empty($signature)) {
            return false;
        }

        $payload = $this->getContent();
        $expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
