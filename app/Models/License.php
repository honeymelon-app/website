<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LicenseStatus;
use Filterable\Contracts\Filterable;
use Filterable\Traits\Filterable as HasFilters;
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
        'status',
        'seats',
        'entitlements',
        'updates_until',
        'meta',
        'order_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'key',
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
            'entitlements' => 'array',
            'updates_until' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get the order associated with the license.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the activations for the license.
     */
    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }
}
