<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UpdateCollection;
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

        return Inertia::render('Admin/Updates/Index', [
            'updates' => new UpdateCollection($updates),
        ]);
    }

    /**
     * Display the specified update.
     */
    public function show(Update $update): Response
    {
        return Inertia::render('Admin/Updates/Show', [
            'update' => new UpdateResource($update->load('release')),
        ]);
    }
}
