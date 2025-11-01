<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtifactResource extends JsonResource
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
            'release_id' => $this->release_id,
            'platform' => $this->platform,
            'source' => $this->source,
            'filename' => $this->filename,
            'size' => $this->size,
            'sha256' => $this->sha256,
            'signature' => $this->signature,
            'notarized' => $this->notarized,
            'url' => $this->url,
            'path' => $this->path,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
