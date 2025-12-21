<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\ArtifactStorage;
use App\Contracts\ArtifactUploader;
use App\Contracts\CheckoutManager;
use App\Contracts\DownloadResolver;
use App\Contracts\GitRepository;
use App\Contracts\LicenseActivator;
use App\Contracts\LicenseManager;
use App\Contracts\PaymentProviderResolver;
use App\Contracts\RefundProcessor;
use App\Contracts\ReleaseManager;
use App\Contracts\WebhookProcessor;
use App\Services\ActivationService;
use App\Services\ArtifactStorageService;
use App\Services\ArtifactUploadService;
use App\Services\CheckoutService;
use App\Services\DownloadService;
use App\Services\GithubService;
use App\Services\LicenseService;
use App\Services\PaymentProviders\PaymentProviderFactory;
use App\Services\RefundService;
use App\Services\ReleaseService;
use App\Services\WebhookProcessingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttributeBindingTest extends TestCase
{
    #[Test]
    #[DataProvider('contractBindingsProvider')]
    public function attribute_based_bindings_resolve_correct_implementation(string $contract, string $implementation): void
    {
        $resolved = $this->app->make($contract);

        $this->assertInstanceOf($implementation, $resolved);
    }

    /**
     * @return array<string, array{0: class-string, 1: class-string}>
     */
    public static function contractBindingsProvider(): array
    {
        return [
            'LicenseManager resolves to LicenseService' => [LicenseManager::class, LicenseService::class],
            'LicenseActivator resolves to ActivationService' => [LicenseActivator::class, ActivationService::class],
            'ReleaseManager resolves to ReleaseService' => [ReleaseManager::class, ReleaseService::class],
            'DownloadResolver resolves to DownloadService' => [DownloadResolver::class, DownloadService::class],
            'ArtifactStorage resolves to ArtifactStorageService' => [ArtifactStorage::class, ArtifactStorageService::class],
            'ArtifactUploader resolves to ArtifactUploadService' => [ArtifactUploader::class, ArtifactUploadService::class],
            'CheckoutManager resolves to CheckoutService' => [CheckoutManager::class, CheckoutService::class],
            'RefundProcessor resolves to RefundService' => [RefundProcessor::class, RefundService::class],
            'WebhookProcessor resolves to WebhookProcessingService' => [WebhookProcessor::class, WebhookProcessingService::class],
            'PaymentProviderResolver resolves to PaymentProviderFactory' => [PaymentProviderResolver::class, PaymentProviderFactory::class],
            'GitRepository resolves to GithubService' => [GitRepository::class, GithubService::class],
        ];
    }

    #[Test]
    public function singleton_services_return_same_instance(): void
    {
        $first = $this->app->make(GithubService::class);
        $second = $this->app->make(GithubService::class);

        $this->assertSame($first, $second);
    }

    #[Test]
    public function payment_provider_factory_is_singleton(): void
    {
        $first = $this->app->make(PaymentProviderFactory::class);
        $second = $this->app->make(PaymentProviderFactory::class);

        $this->assertSame($first, $second);
    }
}
