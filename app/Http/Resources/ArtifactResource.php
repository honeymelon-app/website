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
            'storage_status' => $this->when(isset($this->storage_status), $this->storage_status),
            'download_url' => $this->when(isset($this->download_url), $this->download_url),
            'created_at' => $this->created_at->toIso8601String(),
            'release' => $this->when($this->relationLoaded('release'), function () {
                return (new ReleaseResource($this->release))->resolve();
            }),
        ];
    }
}
