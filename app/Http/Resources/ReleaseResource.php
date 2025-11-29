<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'artifacts' => $this->when($this->relationLoaded('artifacts'), function () {
                return $this->artifacts->map(function ($artifact) {
                    $downloadUrl = $this->generateDownloadUrl($artifact);

                    return [
                        'id' => $artifact->id,
                        'platform' => $artifact->platform,
                        'filename' => $artifact->filename,
                        'size' => $artifact->size,
                        'source' => $artifact->source,
                        'download_url' => $downloadUrl,
                    ];
                });
            }),
        ];
    }

    /**
     * Generate a download URL for an artifact.
     */
    private function generateDownloadUrl($artifact): ?string
    {
        // GitHub-sourced artifacts use the direct URL
        if ($artifact->source === 'github' && $artifact->url) {
            return $artifact->url;
        }

        // For R2/S3 sourced artifacts, generate a temporary signed URL
        if (! empty($artifact->path)) {
            try {
                $disk = Storage::disk('s3');
                if ($disk->exists($artifact->path)) {
                    return $disk->temporaryUrl($artifact->path, now()->addHour());
                }
            } catch (\Exception $e) {
                // Fall back to direct URL if signing fails
                return $artifact->url;
            }
        }

        return $artifact->url;
    }
}
