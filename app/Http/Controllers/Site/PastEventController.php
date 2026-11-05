<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\Events\ListEventsRequest;
use App\Http\Resources\PublicEventResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

final class PastEventController extends Controller
{
    /**
     * List past events, most recent first.
     */
    public function __invoke(ListEventsRequest $request): Response
    {
        $events = Event::query()
            ->visible()
            ->past()
            ->filteredBy(...$request->eventFilters())
            ->latest('starts_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('site/events/Past', [
            'events' => PublicEventResource::collection($events),
            'filters' => $request->selectedFilters(),
            'filterOptions' => $request->filterOptions(),
        ]);
    }
}
