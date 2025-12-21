<?php

declare(strict_types=1);

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProvider;
use Illuminate\Container\Attributes\Singleton;
use InvalidArgumentException;

#[Singleton]
class PaymentProviderFactory
{
    public function __construct(
        private readonly StripePaymentProvider $stripe
    ) {}

    /**
     * Create a payment provider instance by name.
     *
     * @throws InvalidArgumentException
     */
    public function make(string $provider): PaymentProvider
    {
        return match ($provider) {
            'stripe' => $this->stripe,
            default => throw new InvalidArgumentException("Unknown payment provider: {$provider}"),
        };
    }
}
