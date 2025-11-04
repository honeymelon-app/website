<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UpdateResource;
use App\Models\Update;
use Inertia\Inertia;
use Inertia\Response;

class UpdateController extends Controller
{
    /**
     * Display a listing of updates.
     */
    public function index(): Response
    {
        $updates = Update::query()
            ->with('release')
            ->latest('published_at')
            ->paginate(20);

        return Inertia::render('admin/updates/Index', [
            'updates' => [
                'data' => UpdateResource::collection($updates->items())->resolve(),
                'meta' => [
                    'current_page' => $updates->currentPage(),
                    'from' => $updates->firstItem(),
                    'last_page' => $updates->lastPage(),
                    'per_page' => $updates->perPage(),
                    'to' => $updates->lastItem(),
                    'total' => $updates->total(),
                ],
                'links' => [
                    'first' => $updates->url(1),
                    'last' => $updates->url($updates->lastPage()),
                    'prev' => $updates->previousPageUrl(),
                    'next' => $updates->nextPageUrl(),
                ],
            ],
        ]);
    }

    /**
     * Display the specified update.
     */
    public function show(Update $update): Response
    {
        return Inertia::render('admin/updates/Show', [
            'update' => new UpdateResource($update->load('release')),
        ]);
    }
}
