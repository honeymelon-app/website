<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\WebhookEvent;
use App\Services\WebhookProcessingService;
use Illuminate\Container\Attributes\Bind;

#[Bind(WebhookProcessingService::class)]
interface WebhookProcessor
{
    /**
     * Process a payment webhook and issue license if applicable.
     *
     * @param  array{external_id: string, email: string, amount_cents: int, currency: string, status: string, metadata?: array<string, mixed>}  $orderData
     */
    public function processPayment(WebhookEvent $event, array $orderData): void;
}
