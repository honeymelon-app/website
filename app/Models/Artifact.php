<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ArtifactObserver;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ArtifactObserver::class)]
class Artifact extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\ArtifactFactory> */
    use HasFactory, HasFilters, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'platform',
        'source',
        'state',
        'filename',
        'content_type',
        'size',
        'download_count',
        'sha256',
        'signature',
        'notarized',
        'url',
        'path',
        'github_id',
        'github_created_at',
        'github_updated_at',
        'release_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notarized' => 'boolean',
            'github_created_at' => 'datetime',
            'github_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the release that owns the artifact.
     */
    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }
}
