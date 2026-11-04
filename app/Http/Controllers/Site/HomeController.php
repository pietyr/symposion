<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicEventResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    /**
     * Show the home page with the nearest upcoming events.
     */
    public function __invoke(): Response
    {
        $events = Event::query()
            ->published()
            ->upcoming()
            ->oldest('starts_at')
            ->limit(6)
            ->get();

        return Inertia::render('site/Home', [
            'events' => PublicEventResource::collection($events),
        ]);
    }
}
