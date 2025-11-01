<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReleaseResource extends JsonResource
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
            'version' => $this->version,
            'tag' => $this->tag,
            'commit_hash' => $this->commit_hash,
            'channel' => $this->channel->value,
            'notes' => $this->notes,
            'published_at' => $this->published_at?->toIso8601String(),
            'major' => $this->major,
            'created_by' => $this->user_id,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
