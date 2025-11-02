<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Filters\UpdateFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\UpdateCollection;
use App\Http\Resources\UpdateResource;
use App\Models\Update;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    /**
     * Display a listing of updates.
     */
    public function index(Request $request, UpdateFilter $filter): UpdateCollection
    {
        $updates = Update::query()
            ->filter($filter)
            ->paginate($request->input('per_page', 20));

        return new UpdateCollection($updates);
    }

    /**
     * Display the specified update.
     */
    public function show(Update $update): UpdateResource
    {
        return new UpdateResource($update);
    }
}
