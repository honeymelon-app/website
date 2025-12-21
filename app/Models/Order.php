<?php

declare(strict_types=1);

namespace App\Models;

use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, HasFilters, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'external_id',
        'email',
        'amount_cents',
        'currency',
        'meta',
        'user_id',
        'product_id',
        'refund_id',
        'refunded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'meta' => 'array',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Get the user who made the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that was ordered.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the license associated with the order.
     */
    public function license(): HasOne
    {
        return $this->hasOne(License::class);
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        if (! $this->amount_cents) {
            return '$0.00';
        }

        return '$'.number_format($this->amount_cents / 100, 2);
    }

    /**
     * Check if the order has been refunded.
     */
    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }

    /**
     * Check if the order is within the refund window (30 days).
     */
    public function isWithinRefundWindow(): bool
    {
        return $this->created_at->addDays(30)->isFuture();
    }

    /**
     * Check if the order can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return ! $this->isRefunded()
            && $this->provider === 'stripe'
            && $this->amount_cents > 0;
    }

    /**
     * Scope orders created within the last N days.
     */
    #[Scope]
    public function scopeWithinDays(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope orders created between two dates.
     */
    #[Scope]
    public function scopeBetweenDates(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
