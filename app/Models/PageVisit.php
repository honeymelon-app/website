<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    /** @use HasFactory<\Database\Factories\PageVisitFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'path',
        'route_name',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'device_type',
        'browser',
        'platform',
        'session_id',
    ];

    /**
     * Scope for visits within a number of days.
     */
    public function scopeWithinDays(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for visits today.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for unique visitors by session.
     */
    public function scopeUniqueVisitors(Builder $query): Builder
    {
        return $query->distinct('session_id');
    }
}
