<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\License;
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
 * - max_major_version: Filter licenses capped to a specific major version
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
        'max_major_version',
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
        'max_major_version' => ['nullable', 'integer', 'min:1'],
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

    protected function maxMajorVersion(string|int $value): Builder
    {
        $major = (int) $value;

        return $this->getBuilder()->where('max_major_version', '<=', $major);
    }

    /**
     * Search for licenses by the last 4 characters of the license key.
     *
     * Security-conscious: Only allows searching the last 4 characters
     * to prevent full key exposure in logs/queries.
     */
    protected function search(string $value): Builder
    {
        return $this->getBuilder()->where('key_plain', 'like', "%{$value}");
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
            'max_major_version',
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
                'key',
                'key_plain',
                'status',
                'max_major_version',
                'meta',
                'order_id',
                'created_at',
                'updated_at',
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
