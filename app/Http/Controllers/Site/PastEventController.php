<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicEventResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

final class PastEventController extends Controller
{
    /**
     * List past events, most recent first.
     */
    public function __invoke(): Response
    {
        $events = Event::query()
            ->visible()
            ->past()
            ->latest('starts_at')
            ->paginate(12);

        return Inertia::render('site/events/Past', [
            'events' => PublicEventResource::collection($events),
        ]);
    }
}
