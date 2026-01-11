<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ReleaseChannel;
use App\Filters\LicenseFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminResetLicenseActivationRequest;
use App\Http\Requests\AdminRevokeLicenseRequest;
use App\Http\Requests\StoreLicenseRequest;
use App\Http\Resources\LicenseResource;
use App\Models\License;
use App\Models\Order;
use App\Models\Release;
use App\Services\LicenseService;
use App\Support\IndexQueryParams;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    private const SORTABLE_COLUMNS = [
        'status',
        'max_major_version',
        'issued_at',
        'created_at',
    ];

    /**
     * Display a listing of licenses.
     */
    public function index(Request $request, LicenseFilter $filter): Response
    {
        $params = IndexQueryParams::fromRequest(
            request: $request,
            sortableColumns: self::SORTABLE_COLUMNS,
        );

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

        $query = License::query()
            ->with('order')
            ->filter($filter);

        if ($params->sortColumn !== null) {
            $query->orderBy($params->sortColumn, $params->sortDirection);
        } else {
            $query->latest('created_at');
        }

        $licenses = $query->paginate($params->pageSize)->withQueryString();

        return Inertia::render('admin/licenses/Index', [
            'licenses' => [
                'data' => LicenseResource::collection($licenses->items())->resolve(),
                'meta' => [
                    'current_page' => $licenses->currentPage(),
                    'from' => $licenses->firstItem(),
                    'last_page' => $licenses->lastPage(),
                    'per_page' => $licenses->perPage(),
                    'to' => $licenses->lastItem(),
                    'total' => $licenses->total(),
                ],
                'links' => [
                    'first' => $licenses->url(1),
                    'last' => $licenses->url($licenses->lastPage()),
                    'prev' => $licenses->previousPageUrl(),
                    'next' => $licenses->nextPageUrl(),
                ],
            ],
            'filters' => $request->only([
                'status',
                'order_id',
                'max_major_version',
                'search',
            ]),
            'sorting' => [
                'column' => $params->sortColumn,
                'direction' => $params->sortDirection,
            ],
            'pagination' => [
                'pageSize' => $params->pageSize,
                'allowedPageSizes' => IndexQueryParams::ALLOWED_PAGE_SIZES,
            ],
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
            'provider' => 'manual',
            'external_id' => 'admin-'.now()->timestamp,
            'email' => $request->validated('email'),
            'amount_cents' => 0,
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
            return $this->handleWebException(
                $e,
                'admin.licenses.show',
                'Failed to revoke license',
                ['license_id' => $license->id],
                [$license]
            );
        }
    }

    /**
     * Reset a license activation.
     * Allows the license to be activated again on a different device.
     */
    public function resetActivation(
        AdminResetLicenseActivationRequest $request,
        License $license,
        LicenseService $licenseService
    ): RedirectResponse {
        try {
            $licenseService->resetActivation($license);

            return redirect()
                ->route('admin.licenses.show', $license)
                ->with('success', 'License activation has been reset. The license can now be activated on a new device.');
        } catch (\Exception $e) {
            return $this->handleWebException(
                $e,
                'admin.licenses.show',
                'Failed to reset license activation',
                ['license_id' => $license->id],
                [$license]
            );
        }
    }
}
