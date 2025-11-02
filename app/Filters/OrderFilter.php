<?php

declare(strict_types=1);

namespace App\Filters;

use Carbon\Carbon;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter class for Order model.
 *
 * Provides comprehensive filtering capabilities for orders including:
 * - Provider filtering (ls, stripe)
 * - Email search
 * - Amount range filtering (min/max in cents)
 * - Currency filtering
 * - License existence checking
 * - Date range filtering
 *
 * Features enabled:
 * - validation
 * - optimization
 * - filterChaining
 * - valueTransformation
 * - caching
 */
class OrderFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'provider',
        'email',
        'min_amount',
        'max_amount',
        'currency',
        'has_license',
        'created_after',
        'created_before',
    ];

    /**
     * Validation rules for filter inputs.
     *
     * @var array<string, string|array<string>>
     */
    protected array $validationRules = [
        'provider' => 'nullable|in:ls,stripe',
        'email' => 'nullable|email',
        'min_amount' => 'nullable|integer|min:0',
        'max_amount' => 'nullable|integer|min:0',
        'currency' => 'nullable|string|size:3',
        'has_license' => 'nullable|boolean',
        'created_after' => 'nullable|date',
        'created_before' => 'nullable|date',
    ];

    /**
     * Value transformers for filter inputs.
     *
     * @var array<string, callable>
     */
    protected array $transformers = [
        'min_amount' => 'intval',
        'max_amount' => 'intval',
        'has_license' => 'boolval',
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
        'external_id',
        'email',
        'amount_cents',
        'currency',
    ];

    /**
     * Relationships to eager load for query optimization.
     *
     * @var array<int, string>
     */
    protected array $eagerLoad = ['license'];

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
     * Filter by payment provider.
     */
    protected function provider(string $value): Builder
    {
        return $this->getBuilder()->where('provider', $value);
    }

    /**
     * Filter by email address.
     */
    protected function email(string $value): Builder
    {
        return $this->getBuilder()->where('email', $value);
    }

    /**
     * Filter by minimum amount in cents.
     */
    protected function minAmount(int $value): Builder
    {
        return $this->getBuilder()->where('amount_cents', '>=', $value);
    }

    /**
     * Filter by maximum amount in cents.
     */
    protected function maxAmount(int $value): Builder
    {
        return $this->getBuilder()->where('amount_cents', '<=', $value);
    }

    /**
     * Filter by currency code.
     */
    protected function currency(string $value): Builder
    {
        return $this->getBuilder()->where('currency', strtolower($value));
    }

    /**
     * Filter by license existence.
     *
     * @param  bool  $value  True to filter orders with licenses, false for orders without
     */
    protected function hasLicense(bool $value): Builder
    {
        return $this->getBuilder()->when(
            $value,
            fn ($query) => $query->has('license'),
            fn ($query) => $query->doesntHave('license')
        );
    }

    /**
     * Filter by orders created after a specific date.
     */
    protected function createdAfter(Carbon $value): Builder
    {
        return $this->getBuilder()->where('created_at', '>=', $value);
    }

    /**
     * Filter by orders created before a specific date.
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
