<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ReleaseChannel;
use App\Jobs\ProcessGithubReleaseJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GithubWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function getWebhookPayload(array $overrides = []): array
    {
        return array_merge([
            'tag' => 'v1.0.0',
            'version' => '1.0.0',
            'channel' => 'stable',
            'commit_hash' => 'abc123def456789',
            'is_major' => false,
        ], $overrides);
    }

    protected function generateGithubSignature(string $payload, string $secret): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    public function test_webhook_accepts_valid_request_with_github_signature(): void
    {
        Queue::fake();

        $secret = 'test-webhook-secret';
        config(['services.github.webhook_secret' => $secret]);

        $payload = $this->getWebhookPayload();
        $jsonPayload = json_encode($payload);
        $signature = $this->generateGithubSignature($jsonPayload, $secret);

        $response = $this->postJson('/api/webhooks/github/release', $payload, [
            'X-Hub-Signature-256' => $signature,
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Release webhook received and queued for processing',
                'tag' => 'v1.0.0',
                'version' => '1.0.0',
                'channel' => 'stable',
            ]);

        Queue::assertPushed(ProcessGithubReleaseJob::class, function ($job) {
            return $job->tag === 'v1.0.0'
                && $job->version === '1.0.0'
                && $job->channel === ReleaseChannel::STABLE
                && $job->commitHash === 'abc123def456789'
                && $job->isMajor === false;
        });
    }

    public function test_webhook_rejects_invalid_github_signature(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => 'correct-secret']);

        $payload = $this->getWebhookPayload();
        $jsonPayload = json_encode($payload);
        $signature = $this->generateGithubSignature($jsonPayload, 'wrong-secret');

        $response = $this->postJson('/api/webhooks/github/release', $payload, [
            'X-Hub-Signature-256' => $signature,
        ]);

        $response->assertStatus(403);

        Queue::assertNotPushed(ProcessGithubReleaseJob::class);
    }

    public function test_webhook_rejects_missing_github_signature(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = $this->getWebhookPayload();

        $response = $this->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(403);

        Queue::assertNotPushed(ProcessGithubReleaseJob::class);
    }

    public function test_webhook_falls_back_to_sanctum_auth_when_no_secret_configured(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $user = User::factory()->create();

        $payload = $this->getWebhookPayload();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(202);

        Queue::assertPushed(ProcessGithubReleaseJob::class);
    }

    public function test_webhook_rejects_unauthenticated_request_when_no_secret_configured(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $payload = $this->getWebhookPayload();

        $response = $this->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(403);

        Queue::assertNotPushed(ProcessGithubReleaseJob::class);
    }

    public function test_webhook_validates_required_fields(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/webhooks/github/release', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tag', 'version', 'channel', 'commit_hash']);

        Queue::assertNotPushed(ProcessGithubReleaseJob::class);
    }

    public function test_webhook_validates_channel_values(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $user = User::factory()->create();

        $payload = $this->getWebhookPayload(['channel' => 'invalid']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel']);

        Queue::assertNotPushed(ProcessGithubReleaseJob::class);
    }

    public function test_webhook_accepts_beta_channel(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $user = User::factory()->create();

        $payload = $this->getWebhookPayload(['channel' => 'beta']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(202);

        Queue::assertPushed(ProcessGithubReleaseJob::class, function ($job) {
            return $job->channel === ReleaseChannel::BETA;
        });
    }

    public function test_webhook_accepts_major_release_flag(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $user = User::factory()->create();

        $payload = $this->getWebhookPayload(['is_major' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(202);

        Queue::assertPushed(ProcessGithubReleaseJob::class, function ($job) {
            return $job->isMajor === true;
        });
    }

    public function test_webhook_defaults_major_flag_to_false(): void
    {
        Queue::fake();

        config(['services.github.webhook_secret' => null]);

        $user = User::factory()->create();

        $payload = $this->getWebhookPayload();
        unset($payload['is_major']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/webhooks/github/release', $payload);

        $response->assertStatus(202);

        Queue::assertPushed(ProcessGithubReleaseJob::class, function ($job) {
            return $job->isMajor === false;
        });
    }
}
