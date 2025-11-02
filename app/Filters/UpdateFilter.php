<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\Update;
use Carbon\Carbon;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * UpdateFilter
 *
 * Comprehensive filter for the Update model with validation, optimization,
 * and value transformation capabilities.
 *
 * Available Filters:
 * - channel: Filter by release channel (stable, beta)
 * - version: Filter by version string
 * - is_latest: Filter by latest flag
 * - release_id: Filter by release UUID
 * - published_after: Filter updates published after a date
 * - published_before: Filter updates published before a date
 * - search: Search across version field
 * - sort: Sort by allowed columns
 */
class UpdateFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'channel',
        'version',
        'is_latest',
        'release_id',
        'published_after',
        'published_before',
        'search',
        'sort',
    ];

    /**
     * Validation rules for filter parameters.
     *
     * @var array<string, array<int, string>>
     */
    protected array $validationRules = [
        'channel' => ['nullable', 'in:stable,beta'],
        'version' => ['nullable', 'string', 'max:255'],
        'is_latest' => ['nullable', 'boolean'],
        'release_id' => ['nullable', 'uuid'],
        'published_after' => ['nullable', 'date'],
        'published_before' => ['nullable', 'date'],
        'search' => ['nullable', 'string', 'max:255'],
        'sort' => ['nullable', 'string'],
    ];

    /**
     * Filter by release channel.
     */
    protected function channel(string $value): Builder
    {
        return $this->getBuilder()->where('channel', $value);
    }

    /**
     * Filter by version string.
     */
    protected function version(string $value): Builder
    {
        return $this->getBuilder()->where('version', $value);
    }

    /**
     * Filter by latest flag.
     */
    protected function isLatest(string|bool $value): Builder
    {
        $isLatest = (bool) $value;

        return $this->getBuilder()->where('is_latest', $isLatest);
    }

    /**
     * Filter by release ID.
     */
    protected function releaseId(string $value): Builder
    {
        return $this->getBuilder()->where('release_id', $value);
    }

    /**
     * Filter updates published after a specific date.
     */
    protected function publishedAfter(string $value): Builder
    {
        $date = Carbon::parse($value);

        return $this->getBuilder()->where('published_at', '>=', $date);
    }

    /**
     * Filter updates published before a specific date.
     */
    protected function publishedBefore(string $value): Builder
    {
        $date = Carbon::parse($value);

        return $this->getBuilder()->where('published_at', '<=', $date);
    }

    /**
     * Filter by search term across multiple columns.
     */
    protected function search(string $value): Builder
    {
        return $this->getBuilder()->where(function ($query) use ($value) {
            $query->where('version', 'like', "%{$value}%")
                ->orWhere('channel', 'like', "%{$value}%");
        });
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
            'channel',
            'version',
            'is_latest',
            'published_at',
            'created_at',
            'updated_at',
        ];

        if (! in_array($column, $allowedColumns)) {
            $column = 'published_at';
        }

        return $this->getBuilder()->orderBy($column, $direction);
    }

    /**
     * Register pre-filters and enable features for the Update filter.
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
                'channel',
                'version',
                'is_latest',
                'published_at',
                'release_id',
            ])->with('release');
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
