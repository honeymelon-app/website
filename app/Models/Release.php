<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReleaseChannel;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Release extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\ReleaseFactory> */
    use HasFactory, HasFilters, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'version',
        'tag',
        'commit_hash',
        'channel',
        'notes',
        'published_at',
        'is_downloadable',
        'major',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => ReleaseChannel::class,
            'published_at' => 'datetime',
            'is_downloadable' => 'boolean',
            'major' => 'boolean',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::deleting(function (Release $release) {
            // Delete each artifact individually to trigger their deleting events
            // which will clean up the S3 files
            $release->artifacts()->each(fn (Artifact $artifact) => $artifact->delete());
        });
    }

    /**
     * Get the product that owns the release.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that published the release.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the artifacts for the release.
     */
    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class);
    }

    /**
     * Scope to only stable releases.
     */
    public function scopeStable(Builder $query): Builder
    {
        return $query->where('channel', ReleaseChannel::STABLE);
    }

    /**
     * Scope to only published releases.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Scope to only downloadable releases.
     */
    public function scopeDownloadable(Builder $query): Builder
    {
        return $query->where('is_downloadable', true);
    }

    /**
     * Check if the release is stable.
     */
    public function isStable(): bool
    {
        return $this->channel === ReleaseChannel::STABLE;
    }

    /**
     * Check if the release is published.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
