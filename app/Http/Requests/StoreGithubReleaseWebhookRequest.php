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
     * Normalize GitHub webhook payload before validation.
     */
    protected function prepareForValidation(): void
    {
        $derived = [];

        $tag = null;

        if ($this->filled('tag')) {
            $tag = (string) $this->input('tag');
        } else {
            $tag = $this->extractTagFromPayload();

            if ($tag !== null) {
                $derived['tag'] = $tag;
            }
        }

        if ($tag === null) {
            $tag = $derived['tag'] ?? null;
        }

        if (! $this->filled('version') && $tag !== null) {
            $version = $this->normalizeVersion($tag);

            if ($version !== null) {
                $derived['version'] = $version;
            }
        }

        if (! $this->filled('channel') && $tag !== null) {
            $channel = $this->determineChannel($tag);

            if ($channel !== null) {
                $derived['channel'] = $channel;
            }
        }

        if (! $this->filled('commit_hash')) {
            $commitHash = $this->extractCommitHash();

            if ($commitHash !== null) {
                $derived['commit_hash'] = $commitHash;
            }
        }

        if ($derived !== []) {
            $this->merge($derived);
        }
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

    /**
     * Extract the tag name from the GitHub webhook payload.
     */
    private function extractTagFromPayload(): ?string
    {
        $tag = data_get($this->all(), 'release.tag_name');

        if (is_string($tag) && $tag !== '') {
            return $tag;
        }

        $inputsTag = data_get($this->all(), 'inputs.tag');

        if (is_string($inputsTag) && $inputsTag !== '') {
            return $inputsTag;
        }

        $ref = data_get($this->all(), 'ref');

        if (is_string($ref) && str_starts_with($ref, 'refs/tags/')) {
            return substr($ref, strlen('refs/tags/')) ?: null;
        }

        $tagName = data_get($this->all(), 'tag_name');

        if (is_string($tagName) && $tagName !== '') {
            return $tagName;
        }

        return null;
    }

    /**
     * Normalize the Git tag into a version string.
     */
    private function normalizeVersion(?string $tag): ?string
    {
        if ($tag === null || $tag === '') {
            return null;
        }

        if (str_starts_with($tag, 'refs/tags/')) {
            $tag = substr($tag, strlen('refs/tags/')) ?: '';
        }

        $normalized = preg_replace('/^v+/i', '', $tag);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        return $normalized;
    }

    /**
     * Determine the release channel from the tag name.
     */
    private function determineChannel(?string $tag): ?string
    {
        if ($tag === null || $tag === '') {
            return null;
        }

        if (str_starts_with($tag, 'refs/tags/')) {
            $tag = substr($tag, strlen('refs/tags/')) ?: '';
        }

        return str_contains(strtolower($tag), 'beta')
            ? ReleaseChannel::BETA->value
            : ReleaseChannel::STABLE->value;
    }

    /**
     * Extract the commit hash referenced by the webhook.
     */
    private function extractCommitHash(): ?string
    {
        $headCommit = data_get($this->all(), 'head_commit.id');

        if (is_string($headCommit) && $headCommit !== '') {
            return $headCommit;
        }

        $after = data_get($this->all(), 'after');

        if (is_string($after) && $after !== '') {
            return $after;
        }

        $inputsCommit = data_get($this->all(), 'inputs.commit_hash');

        if (is_string($inputsCommit) && $inputsCommit !== '') {
            return $inputsCommit;
        }

        $releaseCommit = data_get($this->all(), 'release.target_commitish');

        if (is_string($releaseCommit) && $releaseCommit !== '') {
            return $releaseCommit;
        }

        return null;
    }
}
