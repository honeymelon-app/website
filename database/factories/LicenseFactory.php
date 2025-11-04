<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\Order;
use App\Support\LicenseBundle;
use App\Support\LicenseCodec;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    /**
     * @var class-string<License>
     */
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'key' => null,
            'key_plain' => null,
            'status' => LicenseStatus::ACTIVE,
            'max_major_version' => 1,
            'meta' => null,
            'order_id' => Order::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (License $license): void {
            if (! config('license.signing.private_key') || ! config('license.signing.public_key')) {
                if (! function_exists('sodium_crypto_sign_keypair')) {
                    throw new \RuntimeException('Signing keys are not configured and sodium is unavailable to generate a fallback keypair.');
                }

                $keypair = sodium_crypto_sign_keypair();
                config()->set('license.signing.private_key', base64_encode($keypair));
                config()->set('license.signing.public_key', base64_encode(sodium_crypto_sign_publickey($keypair)));
            }

            if (! $license->order_id) {
                $license->order_id = Order::factory()->create()->getKey();
            }

            if (! $license->getKey()) {
                $license->id = (string) Str::uuid();
            }

            $issuedAt = now();
            $bundle = LicenseBundle::create($license, $issuedAt);
            $license->key_plain = $bundle['key'];
            $license->key = hash('sha256', LicenseCodec::normalize($bundle['key']));
            $license->meta = [
                'issued_at' => $issuedAt->toIso8601String(),
                'max_major_version' => $license->max_major_version ?? 1,
                'signature' => base64_encode($bundle['signature']),
                'payload' => base64_encode($bundle['payload']),
                'version' => 1,
            ];
        });
    }

    public function revoked(): self
    {
        return $this->state(fn () => ['status' => LicenseStatus::REVOKED]);
    }

    public function expired(): self
    {
        return $this->state(fn () => ['status' => LicenseStatus::EXPIRED]);
    }
}
