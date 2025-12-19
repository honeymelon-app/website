<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Resources\ArtifactCollection;
use App\Http\Resources\ArtifactResource;
use App\Observers\ArtifactObserver;
use App\Policies\ArtifactPolicy;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ArtifactObserver::class)]
#[UsePolicy(ArtifactPolicy::class)]
#[UseResource(ArtifactResource::class)]
#[UseResourceCollection(ArtifactCollection::class)]
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
        'filename',
        'size',
        'sha256',
        'signature',
        'notarized',
        'url',
        'path',
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
        ];
    }

    /**
     * Get the release that owns the artifact.
     */
    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    /**
     * Scope a query to only include artifacts for a specific platform.
     */
    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }
}
