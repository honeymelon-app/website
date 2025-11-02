<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\Artifact;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

class ArtifactFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'platform',
        'source',
        'notarized',
        'release_id',
        'min_size',
        'max_size',
        'search',
    ];

    /**
     * Validation rules for filter inputs.
     *
     * @var array<string, string|array<int, string>>
     */
    protected array $validationRules = [
        'platform' => 'nullable|string|in:darwin-aarch64,darwin-x86_64',
        'source' => 'nullable|in:github,r2,s3',
        'notarized' => 'nullable|boolean',
        'release_id' => 'nullable|uuid',
        'min_size' => 'nullable|integer|min:0',
        'max_size' => 'nullable|integer|min:0',
        'search' => 'nullable|string|max:255',
    ];

    /**
     * Value transformers for filter inputs.
     *
     * @var array<string, callable>
     */
    protected array $transformers = [
        'notarized' => [self::class, 'transformToBoolean'],
        'min_size' => [self::class, 'transformToInteger'],
        'max_size' => [self::class, 'transformToInteger'],
    ];

    /**
     * Filter by platform.
     */
    protected function platform(string $value): Builder
    {
        return $this->getBuilder()->where('platform', $value);
    }

    /**
     * Filter by source.
     */
    protected function source(string $value): Builder
    {
        return $this->getBuilder()->where('source', $value);
    }

    /**
     * Filter by notarized status.
     */
    protected function notarized(bool $value): Builder
    {
        return $this->getBuilder()->where('notarized', $value);
    }

    /**
     * Filter by release ID.
     */
    protected function releaseId(string $value): Builder
    {
        return $this->getBuilder()->where('release_id', $value);
    }

    /**
     * Filter by minimum file size.
     */
    protected function minSize(int $value): Builder
    {
        return $this->getBuilder()->where('size', '>=', $value);
    }

    /**
     * Filter by maximum file size.
     */
    protected function maxSize(int $value): Builder
    {
        return $this->getBuilder()->where('size', '<=', $value);
    }

    /**
     * Filter by search term across multiple columns.
     */
    protected function search(string $value): Builder
    {
        return $this->getBuilder()->where(function ($query) use ($value) {
            $query->where('filename', 'like', "%{$value}%");
        });
    }

    /**
     * Register pre-filters and optimizations for the Artifact.
     *
     * @return $this
     */
    public function setupFilter(): self
    {
        // Enable features
        $this->enableFeature('validation');
        $this->enableFeature('optimization');
        $this->enableFeature('filterChaining');
        $this->enableFeature('valueTransformation');
        $this->enableFeature('caching');

        // Apply optimizations
        $this->registerPreFilters(function (Builder $query) {
            return $query->select([
                'id',
                'platform',
                'source',
                'filename',
                'size',
                'notarized',
                'url',
                'release_id',
            ])->with(['release']);
        });

        return $this;
    }

    /**
     * Transform value to boolean.
     */
    protected static function transformToBoolean(mixed $value): bool
    {
        return (bool) $value;
    }

    /**
     * Transform value to integer.
     */
    protected static function transformToInteger(mixed $value): int
    {
        return (int) $value;
    }
}
