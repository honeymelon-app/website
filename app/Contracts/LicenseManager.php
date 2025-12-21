<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Container\Attributes\Bind;

#[Bind(LicenseService::class)]
interface LicenseManager
{
    /**
     * Issue a new signed license for an order.
     *
     * @param  array{order_id: string, max_major_version?: int}  $orderData
     */
    public function issue(array $orderData): License;

    /**
     * Check if a license key is valid.
     */
    public function isValid(string $key): bool;

    /**
     * Revoke a license.
     */
    public function revoke(License $license): void;

    /**
     * Find a license by its key.
     */
    public function findByKey(string $key): ?License;
}
