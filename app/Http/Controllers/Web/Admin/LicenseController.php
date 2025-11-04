<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LicenseCollection;
use App\Http\Resources\LicenseResource;
use App\Models\License;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(): Response
    {
        $licenses = License::query()
            ->with('order')
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('Admin/Licenses/Index', [
            'licenses' => new LicenseCollection($licenses),
        ]);
    }

    /**
     * Display the specified license.
     */
    public function show(License $license): Response
    {
        return Inertia::render('Admin/Licenses/Show', [
            'license' => new LicenseResource($license->load('order')),
        ]);
    }
}
