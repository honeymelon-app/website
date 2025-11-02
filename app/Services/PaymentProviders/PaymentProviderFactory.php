<?php

declare(strict_types=1);

namespace App\Services\PaymentProviders;

use App\Contracts\PaymentProvider;
use InvalidArgumentException;

class PaymentProviderFactory
{
    public function __construct(
        private readonly StripePaymentProvider $stripe,
        private readonly LemonSqueezyPaymentProvider $lemonSqueezy
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
            'ls', 'lemonsqueezy' => $this->lemonSqueezy,
            default => throw new InvalidArgumentException("Unknown payment provider: {$provider}"),
        };
    }
}
