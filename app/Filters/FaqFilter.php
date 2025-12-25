<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\Faq;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * FaqFilter
 *
 * Filter for the FAQ model with search and active status filtering.
 *
 * Available Filters:
 * - search: Search in question and answer fields
 * - is_active: Filter by active/inactive status
 * - sort: Sort by allowed columns
 */
class FaqFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'search',
        'is_active',
        'sort',
    ];

    /**
     * Validation rules for filter parameters.
     *
     * @var array<string, array<int, string>>
     */
    protected array $validationRules = [
        'search' => ['nullable', 'string', 'max:255'],
        'is_active' => ['nullable', 'boolean'],
        'sort' => ['nullable', 'string'],
    ];

    /**
     * Filter by search term in question and answer.
     */
    protected function search(string $value): Builder
    {
        return $this->getBuilder()->where(function (Builder $query) use ($value): void {
            $query->where('question', 'LIKE', "%{$value}%")
                ->orWhere('answer', 'LIKE', "%{$value}%");
        });
    }

    /**
     * Filter by active status.
     */
    protected function isActive(bool $value): Builder
    {
        return $this->getBuilder()->where('is_active', $value);
    }
}
