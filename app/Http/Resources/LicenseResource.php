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
            'key' => $this->key_plain,
            'key_hash' => $this->key,
            'status' => $this->status->value,
            'max_major_version' => $this->max_major_version ?? 1,
            'issued_at' => $this->meta['issued_at'] ?? null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
