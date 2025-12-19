<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\ActivationError;
use App\Services\ActivationService;
use Illuminate\Container\Attributes\Bind;

#[Bind(ActivationService::class)]
interface LicenseActivator
{
    /**
     * Attempt to activate a license.
     *
     * @return array{success: bool, license?: array<string, mixed>, error?: string, error_code?: ActivationError}
     */
    public function activate(string $licenseKey, string $appVersion, ?string $deviceId = null): array;
}
