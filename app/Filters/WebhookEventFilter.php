<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\WebhookEvent;
use Carbon\Carbon;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter class for WebhookEvent model.
 *
 * Provides comprehensive filtering capabilities for webhook events including:
 * - Provider filtering (ls, stripe, github)
 * - Event type filtering (order.created, order.updated, etc.)
 * - Processing status filtering
 * - Date range filtering
 *
 * Features enabled:
 * - validation
 * - optimization
 * - filterChaining
 * - valueTransformation
 * - caching
 */
class WebhookEventFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'provider',
        'type',
        'processed',
        'created_after',
        'created_before',
    ];

    /**
     * Validation rules for filter inputs.
     *
     * @var array<string, string|array<string>>
     */
    protected array $validationRules = [
        'provider' => 'nullable|in:ls,stripe,github',
        'type' => 'nullable|in:order.created,order.updated,order.deleted,release.created,release.updated,release.deleted,artifact.created,artifact.deleted',
        'processed' => 'nullable|boolean',
        'created_after' => 'nullable|date',
        'created_before' => 'nullable|date',
    ];

    /**
     * Value transformers for filter inputs.
     *
     * @var array<string, callable>
     */
    protected array $transformers = [
        'processed' => 'boolval',
        'created_after' => [self::class, 'parseDate'],
        'created_before' => [self::class, 'parseDate'],
    ];

    /**
     * Columns to select for query optimization.
     *
     * @var array<int, string>
     */
    protected array $selectColumns = [
        'id',
        'provider',
        'type',
        'processed_at',
        'created_at',
    ];

    /**
     * Setup the filter with enabled features.
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);

        $this->enableFeatures([
            'validation',
            'optimization',
            'filterChaining',
            'valueTransformation',
            'caching',
        ]);
    }

    /**
     * Filter by webhook provider.
     */
    protected function provider(string $value): Builder
    {
        return $this->getBuilder()->where('provider', $value);
    }

    /**
     * Filter by webhook event type.
     */
    protected function type(string $value): Builder
    {
        return $this->getBuilder()->where('type', $value);
    }

    /**
     * Filter by processing status.
     *
     * @param  bool  $value  True to filter processed events, false for unprocessed
     */
    protected function processed(bool $value): Builder
    {
        return $this->getBuilder()->when(
            $value,
            fn ($query) => $query->whereNotNull('processed_at'),
            fn ($query) => $query->whereNull('processed_at')
        );
    }

    /**
     * Filter by events created after a specific date.
     */
    protected function createdAfter(Carbon $value): Builder
    {
        return $this->getBuilder()->where('created_at', '>=', $value);
    }

    /**
     * Filter by events created before a specific date.
     */
    protected function createdBefore(Carbon $value): Builder
    {
        return $this->getBuilder()->where('created_at', '<=', $value);
    }

    /**
     * Parse date string into Carbon instance.
     */
    protected static function parseDate(string $value): Carbon
    {
        return Carbon::parse($value);
    }
}
