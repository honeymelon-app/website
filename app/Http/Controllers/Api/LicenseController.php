<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LicenseCollection;
use App\Http\Resources\LicenseResource;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses.
     */
    public function index(Request $request): LicenseCollection
    {
        $licenses = License::query()
            ->with('order', 'activations')
            ->latest('created_at')
            ->paginate($request->input('per_page', 20));

        return new LicenseCollection($licenses);
    }

    /**
     * Display the specified license.
     */
    public function show(License $license): LicenseResource
    {
        return new LicenseResource($license->load('order', 'activations'));
    }
}
