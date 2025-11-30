<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'email' => $this->email,
            'amount_cents' => $this->amount_cents,
            'formatted_amount' => $this->formatted_amount,
            'currency' => $this->currency,
            'license_id' => $this->license?->id,
            'license' => $this->whenLoaded('license', fn () => (new LicenseResource($this->license))->resolve()),
            'refund_id' => $this->refund_id,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'is_refunded' => $this->isRefunded(),
            'can_be_refunded' => $this->canBeRefunded(),
            'is_within_refund_window' => $this->isWithinRefundWindow(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
