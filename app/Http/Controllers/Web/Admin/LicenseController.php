<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ReleaseChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRevokeLicenseRequest;
use App\Http\Requests\StoreLicenseRequest;
use App\Http\Resources\LicenseCollection;
use App\Http\Resources\LicenseResource;
use App\Models\License;
use App\Models\Order;
use App\Models\Release;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(): Response
    {
        $availableMajorVersions = Release::query()
            ->whereIn('channel', [
                ReleaseChannel::STABLE,
                ReleaseChannel::ALPHA,
            ])
            ->published()
            ->orderByDesc('published_at')
            ->pluck('version')
            ->map(static fn (string $version): int => (int) explode('.', $version)[0])
            ->filter(static fn (int $major): bool => $major >= 0 && $major < 999)
            ->unique()
            ->values()
            ->all();

        $licenses = License::query()
            ->with('order')
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('admin/licenses/Index', [
            'licenses' => new LicenseCollection($licenses),
            'available_versions' => $availableMajorVersions,
        ]);
    }

    /**
     * Store a newly created license.
     */
    public function store(StoreLicenseRequest $request, LicenseService $licenseService): RedirectResponse
    {
        // Create a manual order for admin-issued licenses
        $order = Order::create([
            'id' => (string) Str::uuid(),
            'provider' => 'manual',
            'external_id' => 'admin-'.now()->timestamp,
            'email' => $request->validated('email'),
            'amount' => 0,
            'currency' => 'USD',
            'meta' => [
                'issued_by' => 'admin',
                'issued_at' => now()->toIso8601String(),
            ],
        ]);

        // Issue the license
        $license = $licenseService->issue([
            'order_id' => $order->id,
            'max_major_version' => $request->validated('max_major_version'),
        ]);

        return redirect()
            ->route('admin.licenses.index')
            ->with('license_key', $license->key_plain)
            ->with('license_email', $request->validated('email'));
    }

    /**
     * Display the specified license.
     */
    public function show(License $license): Response
    {
        return Inertia::render('admin/licenses/Show', [
            'license' => (new LicenseResource($license->load('order')))->resolve(),
        ]);
    }

    /**
     * Revoke a license manually.
     */
    public function revoke(AdminRevokeLicenseRequest $request, License $license, LicenseService $licenseService): RedirectResponse
    {
        if (! $license->isActive()) {
            return redirect()
                ->route('admin.licenses.show', $license)
                ->with('error', 'License is already '.$license->status->label().'.');
        }

        try {
            $licenseService->revoke($license);

            return redirect()
                ->route('admin.licenses.show', $license)
                ->with('success', 'License has been revoked successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.licenses.show', $license)
                ->with('error', 'Failed to revoke license: '.$e->getMessage());
        }
    }
}
