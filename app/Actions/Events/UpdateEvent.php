<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;

final class UpdateEvent
{
    public function __construct(private readonly GenerateEventSlug $generateEventSlug) {}

    /**
     * Update an event; a new title or city gives it a new slug, which is safe
     * because public URLs identify the event by its id.
     *
     * @param  array<string, mixed>  $attributes  validated event form data
     */
    public function handle(Event $event, array $attributes): Event
    {
        $event->fill($attributes);
        $event->forgetVenueIfOnline();

        if ($event->isDirty(['title', 'city'])) {
            $event->slug = $this->generateEventSlug->handle($event);
        }

        $event->save();

        return $event;
    }
}
