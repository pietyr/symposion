<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicEventResource;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class EventController extends Controller
{
    /**
     * List upcoming events, nearest first.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->published()
            ->upcoming()
            ->oldest('starts_at')
            ->paginate(12);

        return Inertia::render('site/events/Index', [
            'events' => PublicEventResource::collection($events),
        ]);
    }

    /**
     * Show an event; the id identifies it, so an outdated slug redirects to the current address.
     */
    public function show(Event $event, ?string $slug = null): Response|RedirectResponse
    {
        Gate::authorize('view', $event);

        if ($slug !== $event->slug) {
            return to_route('events.show', ['event' => $event, 'slug' => $event->slug], 301);
        }

        return Inertia::render('site/events/Show', [
            'event' => PublicEventResource::make($event),
        ]);
    }
}
