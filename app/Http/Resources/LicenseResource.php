<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
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
            'key' => $this->key,
            'status' => $this->status->value,
            'seats' => $this->seats,
            'entitlements' => $this->entitlements,
            'updates_until' => $this->updates_until?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
