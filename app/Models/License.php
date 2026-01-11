<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LicenseStatus;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\LicenseFactory> */
    use HasFactory, HasFilters, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'key_plain',
        'status',
        'max_major_version',
        'can_access_prereleases',
        'meta',
        'user_id',
        'product_id',
        'order_id',
        'activated_at',
        'activation_count',
        'device_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'key', // hashed representation
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LicenseStatus::class,
            'max_major_version' => 'integer',
            'can_access_prereleases' => 'boolean',
            'meta' => 'array',
            'activated_at' => 'datetime',
            'activation_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns the license.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product the license is for.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order associated with the license.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the downloads associated with the license.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Scope a query to only include active licenses.
     */
    #[Scope]
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', LicenseStatus::ACTIVE);
    }

    /**
     * Scope a query to only include refunded licenses.
     */
    #[Scope]
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', LicenseStatus::REFUNDED);
    }

    /**
     * Scope a query to only include revoked licenses.
     */
    #[Scope]
    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('status', LicenseStatus::REVOKED);
    }

    /**
     * Scope to only include licenses within the last N days.
     */
    #[Scope]
    public function scopeWithinDays(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to only include licenses within a date range.
     */
    #[Scope]
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if the license is active.
     */
    public function isActive(): bool
    {
        return $this->status === LicenseStatus::ACTIVE;
    }

    /**
     * Check if the license has been refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === LicenseStatus::REFUNDED;
    }

    /**
     * Check if the license has been revoked.
     */
    public function isRevoked(): bool
    {
        return $this->status === LicenseStatus::REVOKED;
    }

    /**
     * Check if the license has already been activated.
     */
    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }

    /**
     * Check if this is a lifetime license (works with any major version).
     */
    public function isLifetime(): bool
    {
        return (int) ($this->max_major_version ?? 0) === 255;
    }

    /**
     * Check if the license can be activated.
     * Lifetime licenses can always be activated if status is active.
     * Version-specific licenses must be active and not already activated.
     */
    public function canActivate(): bool
    {
        if (! $this->status->allowsActivation()) {
            return false;
        }

        return $this->isLifetime() || ! $this->isActivated();
    }

    /**
     * Mark the license as activated.
     *
     * @param  string|null  $deviceId  The device ID to bind (null for lifetime licenses)
     * @param  bool  $isLifetime  Whether this is a lifetime license (allows multiple activations)
     */
    public function markAsActivated(?string $deviceId = null, bool $isLifetime = false): void
    {
        $updates = [
            'activation_count' => $this->activation_count + 1,
        ];

        // Only set activated_at on first activation, or always for non-lifetime licenses
        if (! $this->activated_at || ! $isLifetime) {
            $updates['activated_at'] = now();
        }

        // Only bind device_id for non-lifetime licenses
        if (! $isLifetime && $deviceId !== null) {
            $updates['device_id'] = $deviceId;
        }

        $this->update($updates);
    }

    /**
     * Mark the license as refunded.
     */
    public function markAsRefunded(): void
    {
        $this->update(['status' => LicenseStatus::REFUNDED]);
    }

    /**
     * Mark the license as revoked.
     */
    public function markAsRevoked(): void
    {
        $this->update(['status' => LicenseStatus::REVOKED]);
    }

    /**
     * Check if the license can access a specific release channel.
     */
    public function canAccessChannel(string $channel): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($channel === 'stable') {
            return true;
        }

        return $this->can_access_prereleases;
    }
}
