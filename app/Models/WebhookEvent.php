<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookEvent as WebhookEventEnum;
use App\Http\Resources\WebhookEventCollection;
use App\Http\Resources\WebhookEventResource;
use App\Policies\WebhookEventPolicy;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(WebhookEventPolicy::class)]
#[UseResource(WebhookEventResource::class)]
#[UseResourceCollection(WebhookEventCollection::class)]
class WebhookEvent extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\WebhookEventFactory> */
    use HasFactory, HasFilters, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'type',
        'payload',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
            'type' => WebhookEventEnum::class,
        ];
    }

    /**
     * Scope a query to only include events for a specific provider.
     */
    public function scopeForProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope a query to only include processed events.
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->whereNotNull('processed_at');
    }

    /**
     * Scope a query to only include unprocessed events.
     */
    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->whereNull('processed_at');
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeOfType(Builder $query, WebhookEventEnum $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if the webhook event has been processed.
     */
    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    /**
     * Mark the webhook event as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }
}
