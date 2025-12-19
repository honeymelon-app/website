<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Services\PaymentProviders\PaymentProviderFactory;
use Illuminate\Container\Attributes\Bind;

#[Bind(PaymentProviderFactory::class)]
interface PaymentProviderResolver
{
    /**
     * Create a payment provider instance by name.
     *
     * @throws \InvalidArgumentException
     */
    public function make(string $provider): PaymentProvider;
}
