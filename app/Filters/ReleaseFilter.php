<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\ReleaseChannel;
use App\Models\Release;
use Carbon\Carbon;
use Filterable\Filter;
use Illuminate\Database\Eloquent\Builder;

class ReleaseFilter extends Filter
{
    /**
     * Registered filters to operate upon.
     *
     * @var array<int, string>
     */
    protected array $filters = [
        'version',
        'tag',
        'channel',
        'major',
        'published_after',
        'published_before',
        'user_id',
        'search',
    ];

    /**
     * Validation rules for filter inputs.
     *
     * @var array<string, string|array<int, string>>
     */
    protected array $validationRules = [
        'version' => 'nullable|string|max:255',
        'tag' => 'nullable|string|max:255',
        'channel' => 'nullable|in:stable,beta',
        'major' => 'nullable|boolean',
        'published_after' => 'nullable|date',
        'published_before' => 'nullable|date',
        'user_id' => 'nullable|uuid',
        'search' => 'nullable|string|max:255',
    ];

    /**
     * Value transformers for filter inputs.
     *
     * @var array<string, callable>
     */
    protected array $transformers = [
        'published_after' => [self::class, 'transformToCarbon'],
        'published_before' => [self::class, 'transformToCarbon'],
        'major' => [self::class, 'transformToBoolean'],
    ];

    /**
     * Filter by version.
     */
    protected function version(string $value): Builder
    {
        return $this->getBuilder()->where('version', $value);
    }

    /**
     * Filter by tag.
     */
    protected function tag(string $value): Builder
    {
        return $this->getBuilder()->where('tag', $value);
    }

    /**
     * Filter by release channel.
     */
    protected function channel(string $value): Builder
    {
        $channel = ReleaseChannel::tryFrom($value);

        if ($channel === null) {
            return $this->getBuilder();
        }

        return $this->getBuilder()->where('channel', $channel);
    }

    /**
     * Filter by major release flag.
     */
    protected function major(bool $value): Builder
    {
        return $this->getBuilder()->where('major', $value);
    }

    /**
     * Filter by releases published after a date.
     */
    protected function publishedAfter(Carbon $value): Builder
    {
        return $this->getBuilder()->where('published_at', '>=', $value);
    }

    /**
     * Filter by releases published before a date.
     */
    protected function publishedBefore(Carbon $value): Builder
    {
        return $this->getBuilder()->where('published_at', '<=', $value);
    }

    /**
     * Filter by user ID.
     */
    protected function userId(string $value): Builder
    {
        return $this->getBuilder()->where('user_id', $value);
    }

    /**
     * Filter by search term across multiple columns.
     */
    protected function search(string $value): Builder
    {
        return $this->getBuilder()->where(function ($query) use ($value) {
            $query->where('version', 'like', "%{$value}%")
                ->orWhere('tag', 'like', "%{$value}%")
                ->orWhere('notes', 'like', "%{$value}%");
        });
    }

    /**
     * Register pre-filters and optimizations for the Release.
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
                'version',
                'tag',
                'channel',
                'notes',
                'published_at',
                'major',
                'user_id',
            ])->with(['user', 'artifacts', 'updates']);
        });

        return $this;
    }

    /**
     * Transform value to Carbon instance.
     */
    protected static function transformToCarbon(string $value): Carbon
    {
        return Carbon::parse($value);
    }

    /**
     * Transform value to boolean.
     */
    protected static function transformToBoolean(mixed $value): bool
    {
        return (bool) $value;
    }
}
