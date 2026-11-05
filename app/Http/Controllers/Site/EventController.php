<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicEventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class EventController extends Controller
{
    /**
     * List upcoming events, nearest first.
     */
    public function index(): Response
    {
        $events = Event::query()
            ->visible()
            ->upcoming()
            ->oldest('starts_at')
            ->paginate(12);

        return Inertia::render('site/events/Index', [
            'events' => PublicEventResource::collection($events),
        ]);
    }

    /**
     * Show an event; the id identifies it, so an outdated slug redirects to the current address.
     *
     * Hidden events are open to anyone with the link, but must not reach search engines.
     * The header is set here because without SSR a meta tag would only exist in the browser.
     */
    public function show(Request $request, Event $event, ?string $slug = null): SymfonyResponse
    {
        if ($slug !== $event->slug) {
            return to_route('events.show', ['event' => $event, 'slug' => $event->slug], 301);
        }

        $response = Inertia::render('site/events/Show', [
            'event' => PublicEventResource::make($event->load([
                'professionEntries',
                'specializations' => fn ($query) => $query->orderBy('name'),
            ])),
        ])->toResponse($request);

        if (! $event->is_visible) {
            $response->headers->set('X-Robots-Tag', 'noindex');
        }

        return $response;
    }
}
