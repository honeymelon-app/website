<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Artifact;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArtifactObserver
{
    /**
     * Handle the Artifact "deleted" event.
     *
     * Delete the corresponding R2 object when an artifact is deleted.
     */
    public function deleted(Artifact $artifact): void
    {
        if (empty($artifact->path)) {
            return;
        }

        // Only delete from R2 if source is r2 or s3
        if (! in_array($artifact->source, ['r2', 's3'])) {
            return;
        }

        try {
            $disk = Storage::disk('s3');

            if ($disk->exists($artifact->path)) {
                $disk->delete($artifact->path);

                Log::info('Deleted R2 object for artifact', [
                    'artifact_id' => $artifact->id,
                    'path' => $artifact->path,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete R2 object for artifact', [
                'artifact_id' => $artifact->id,
                'path' => $artifact->path,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
