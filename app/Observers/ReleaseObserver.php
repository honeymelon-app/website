<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Artifact;
use App\Models\Release;

class ReleaseObserver
{
    /**
     * Handle the Release "deleting" event.
     *
     * Delete each artifact individually to trigger their deleting events
     * which will clean up the S3 files.
     */
    public function deleting(Release $release): void
    {
        $release->artifacts()->each(fn (Artifact $artifact) => $artifact->delete());
    }
}
