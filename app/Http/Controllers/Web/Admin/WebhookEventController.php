<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Constants\DateRanges;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookEventCollection;
use App\Http\Resources\WebhookEventResource;
use App\Models\WebhookEvent;
use Inertia\Inertia;
use Inertia\Response;

class WebhookEventController extends Controller
{
    /**
     * Display a listing of webhook events.
     */
    public function index(): Response
    {
        $events = WebhookEvent::query()
            ->latest('created_at')
            ->paginate(DateRanges::ADMIN_PAGINATION_SIZE);

        return Inertia::render('Admin/WebhookEvents/Index', [
            'events' => new WebhookEventCollection($events),
        ]);
    }

    /**
     * Display the specified webhook event.
     */
    public function show(WebhookEvent $webhookEvent): Response
    {
        return Inertia::render('Admin/WebhookEvents/Show', [
            'event' => new WebhookEventResource($webhookEvent),
        ]);
    }
}
