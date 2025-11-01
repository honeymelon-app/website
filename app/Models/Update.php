<?php

namespace App\Models;

use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Update extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\UpdateFactory> */
    use HasFactory, HasFilters, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'channel',
        'version',
        'manifest',
        'is_latest',
        'published_at',
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
            'is_latest' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the release that owns the update.
     */
    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }
}
