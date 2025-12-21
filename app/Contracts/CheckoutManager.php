<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Services\CheckoutService;
use Illuminate\Container\Attributes\Bind;

#[Bind(CheckoutService::class)]
interface CheckoutManager
{
    /**
     * Create a checkout session for license purchase.
     *
     * @param  array{provider: string, product_slug?: string, success_url: string, cancel_url: string, email?: string, metadata?: array<string, mixed>}  $data
     * @return array{checkout_url: string, session_id: string, provider: string}
     */
    public function createCheckoutSession(array $data): array;
}
