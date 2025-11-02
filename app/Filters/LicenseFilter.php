<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\License;
use Carbon\Carbon;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * LicenseFilter
 *
 * Comprehensive filter for the License model with validation, optimization,
 * and value transformation capabilities. Includes security-conscious handling
 * of license key searches.
 *
 * Available Filters:
 * - status: Filter by license status (active, revoked, expired)
 * - order_id: Filter by order UUID
 * - min_seats: Filter licenses with minimum number of seats
 * - max_seats: Filter licenses with maximum number of seats
 * - has_entitlement: Filter licenses containing specific entitlement
 * - updates_after: Filter licenses with updates_until >= date
 * - updates_before: Filter licenses with updates_until <= date
 * - search: Search last 4 characters of license key (security-conscious)
 * - sort: Sort by allowed columns
 */
class LicenseFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'status',
        'order_id',
        'min_seats',
        'max_seats',
        'has_entitlement',
        'updates_after',
        'updates_before',
        'search',
        'sort',
    ];

    /**
     * Validation rules for filter parameters.
     *
     * @var array<string, array<int, string>>
     */
    protected array $validationRules = [
        'status' => ['nullable', 'in:active,revoked,expired'],
        'order_id' => ['nullable', 'uuid'],
        'min_seats' => ['nullable', 'integer', 'min:1'],
        'max_seats' => ['nullable', 'integer', 'min:1'],
        'has_entitlement' => ['nullable', 'string'],
        'updates_after' => ['nullable', 'date'],
        'updates_before' => ['nullable', 'date'],
        'search' => ['nullable', 'string', 'max:4'],
        'sort' => ['nullable', 'string'],
    ];

    /**
     * Filter by license status.
     */
    protected function status(string $value): Builder
    {
        return $this->getBuilder()->where('status', $value);
    }

    /**
     * Filter by order ID.
     */
    protected function orderId(string $value): Builder
    {
        return $this->getBuilder()->where('order_id', $value);
    }

    /**
     * Filter licenses with minimum number of seats.
     */
    protected function minSeats(string|int $value): Builder
    {
        $seats = (int) $value;

        return $this->getBuilder()->where('seats', '>=', $seats);
    }

    /**
     * Filter licenses with maximum number of seats.
     */
    protected function maxSeats(string|int $value): Builder
    {
        $seats = (int) $value;

        return $this->getBuilder()->where('seats', '<=', $seats);
    }

    /**
     * Filter licenses that have a specific entitlement.
     *
     * Uses JSON contains query to check if the entitlement exists
     * in the entitlements array.
     */
    protected function hasEntitlement(string $value): Builder
    {
        return $this->getBuilder()->whereJsonContains('entitlements', $value);
    }

    /**
     * Filter licenses with updates available after a specific date.
     */
    protected function updatesAfter(string $value): Builder
    {
        $date = Carbon::parse($value);

        return $this->getBuilder()->where('updates_until', '>=', $date);
    }

    /**
     * Filter licenses with updates expiring before a specific date.
     */
    protected function updatesBefore(string $value): Builder
    {
        $date = Carbon::parse($value);

        return $this->getBuilder()->where('updates_until', '<=', $date);
    }

    /**
     * Search for licenses by the last 4 characters of the license key.
     *
     * Security-conscious: Only allows searching the last 4 characters
     * to prevent full key exposure in logs/queries.
     */
    protected function search(string $value): Builder
    {
        return $this->getBuilder()->where('key', 'like', "%{$value}");
    }

    /**
     * Sort the results by a given column and direction.
     */
    protected function sort(string $value): Builder
    {
        [$column, $direction] = array_pad(explode(':', $value), 2, 'asc');
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'asc';

        $allowedColumns = [
            'id',
            'status',
            'seats',
            'updates_until',
            'created_at',
            'updated_at',
        ];

        if (! in_array($column, $allowedColumns)) {
            $column = 'created_at';
        }

        return $this->getBuilder()->orderBy($column, $direction);
    }

    /**
     * Register pre-filters and enable features for the License filter.
     */
    public function setupFilter(): self
    {
        // Enable validation
        $this->enableFeature('validation');

        // Enable optimization with specific selects and eager loading
        $this->enableFeature('optimization');
        $this->registerPreFilters(function (Builder $query) {
            return $query->select([
                'id',
                'status',
                'seats',
                'entitlements',
                'updates_until',
                'order_id',
            ])->with('order');
        });

        // Enable filter chaining
        $this->enableFeature('filterChaining');

        // Enable value transformation
        $this->enableFeature('valueTransformation');

        // Enable caching
        $this->enableFeature('caching');

        return $this;
    }
}
