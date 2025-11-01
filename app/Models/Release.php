<?php

namespace App\Models;

use App\Enums\ReleaseChannel;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'version',
        'tag',
        'commit_hash',
        'channel',
        'notes',
        'published_at',
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
            'major' => 'boolean',
        ];
    }

    /**
     * Get the user that published the release.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
