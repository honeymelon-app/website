<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\User;
use Carbon\Carbon;
use Filterable\Filter;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

/**
 * Filter class for Order model.
 *
 * Provides comprehensive filtering capabilities for orders including:
 * - Provider filtering (ls, stripe)
 * - Email search (exact or partial)
 * - Amount range filtering (min/max in cents)
 * - Currency filtering
 * - License existence checking
 * - Date range filtering
 *
 * @method self forUser(User $user) Scope filters to a specific user context
 * @method self setCacheExpiration(int $seconds) Set cache duration
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
        'email_search',
        'min_amount',
        'max_amount',
        'currency',
        'has_license',
        'license_status',
        'created_after',
        'created_before',
    ];

    /**
     * Setup the filter with enabled features and configuration.
     */
    public function __construct(
        Request $request,
        ?Cache $cache = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($request, $cache, $logger);

        $this->configureFeatures()
            ->configureValidation()
            ->configureTransformers()
            ->configureOptimization()
            ->configureCaching();
    }

    /**
     * Configure enabled features for the filter.
     */
    protected function configureFeatures(): self
    {
        $this->enableFeatures([
            'validation',
            'optimization',
            'filterChaining',
            'valueTransformation',
            'caching',
        ]);

        return $this;
    }

    /**
     * Configure validation rules for filter inputs.
     */
    protected function configureValidation(): self
    {
        $this->setValidationRules([
            'provider' => ['nullable', 'in:ls,stripe'],
            'email' => ['nullable', 'email'],
            'email_search' => ['nullable', 'string', 'min:3'],
            'min_amount' => ['nullable', 'integer', 'min:0'],
            'max_amount' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'has_license' => ['nullable', 'in:yes,no'],
            'license_status' => ['nullable', 'in:with,without'],
            'created_after' => ['nullable', 'date'],
            'created_before' => ['nullable', 'date', 'after_or_equal:created_after'],
        ]);

        return $this;
    }

    /**
     * Configure value transformers for filter inputs.
     *
     * Note: Due to how the parent Filter class works, transformers are
     * called but then getFilterables() overwrites them when applying.
     * Instead, we handle transformations directly in filter methods.
     */
    protected function configureTransformers(): self
    {
        // Amount transformers work because they're cast to int in the method signature
        // Date transformers need to be applied in the filter methods
        $this->registerTransformer('currency', fn ($value) => strtoupper($value));

        return $this;
    }

    /**
     * Configure query optimization settings.
     */
    protected function configureOptimization(): self
    {
        $this->select([
            'id',
            'provider',
            'external_id',
            'email',
            'amount_cents',
            'currency',
            'created_at',
        ]);

        $this->with(['license:id,order_id,key,status']);

        return $this;
    }

    /**
     * Configure caching settings.
     */
    protected function configureCaching(): self
    {
        $this->setCacheExpiration(300);

        return $this;
    }

    /**
     * Filter by payment provider.
     */
    protected function provider(string $value): void
    {
        $this->getBuilder()->where('provider', $value);
    }

    /**
     * Filter by exact email address.
     */
    protected function email(string $value): void
    {
        $this->getBuilder()->where('email', $value);
    }

    /**
     * Filter by partial email address match (LIKE search).
     */
    protected function emailSearch(string $value): void
    {
        $this->getBuilder()->where('email', 'LIKE', "%{$value}%");
    }

    /**
     * Filter by minimum amount in cents.
     */
    protected function minAmount(string|int $value): void
    {
        $this->getBuilder()->where('amount_cents', '>=', (int) $value);
    }

    /**
     * Filter by maximum amount in cents.
     */
    protected function maxAmount(string|int $value): void
    {
        $this->getBuilder()->where('amount_cents', '<=', (int) $value);
    }

    /**
     * Filter by currency code.
     */
    protected function currency(string $value): void
    {
        $this->getBuilder()->where('currency', strtolower($value));
    }

    /**
     * Filter by license existence using yes/no string values.
     *
     * @param  string  $value  'yes' to filter orders with licenses, 'no' for orders without
     */
    protected function hasLicense(string $value): void
    {
        if ($value === 'yes') {
            $this->getBuilder()->has('license');
        } else {
            $this->getBuilder()->doesntHave('license');
        }
    }

    /**
     * Filter by license status using with/without string values.
     *
     * @param  string  $value  'with' to filter orders with licenses, 'without' for orders without
     */
    protected function licenseStatus(string $value): void
    {
        if ($value === 'with') {
            $this->getBuilder()->has('license');
        } else {
            $this->getBuilder()->doesntHave('license');
        }
    }

    /**
     * Filter by orders created after a specific date.
     */
    protected function createdAfter(string $value): void
    {
        $date = Carbon::parse($value)->startOfDay();
        $this->getBuilder()->where('created_at', '>=', $date);
    }

    /**
     * Filter by orders created before a specific date.
     */
    protected function createdBefore(string $value): void
    {
        $date = Carbon::parse($value)->endOfDay();
        $this->getBuilder()->where('created_at', '<=', $date);
    }
}
