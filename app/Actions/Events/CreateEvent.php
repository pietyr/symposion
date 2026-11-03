<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;

final class CreateEvent
{
    public function __construct(private readonly GenerateEventSlug $generateEventSlug) {}

    /**
     * Create an event with a slug derived from its title and city.
     *
     * @param  array<string, mixed>  $attributes  validated event form data
     */
    public function handle(array $attributes): Event
    {
        $event = new Event($attributes);
        $event->forgetVenueIfOnline();
        $event->slug = $this->generateEventSlug->handle($event);
        $event->save();

        return $event;
    }
}
