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
            'currency' => $this->currency,
            'license_id' => $this->license?->id,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
