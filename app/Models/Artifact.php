<?php

namespace App\Models;

use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'signature' => 'hashed',
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
}
