<?php

namespace App\Models;

use App\Enums\WebhookEvent as WebhookEventEnum;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model implements Filterable
{
    /** @use HasFactory<\Database\Factories\WebhookEventFactory> */
    use HasFactory, HasFilters;

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
}
