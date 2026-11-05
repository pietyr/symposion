<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

final class CreateEvent
{
    public function __construct(
        private readonly GenerateEventSlug $generateEventSlug,
        private readonly SyncEventProfessions $syncEventProfessions,
    ) {}

    /**
     * Create an event with a slug derived from its title and city.
     *
     * @param  array<string, mixed>  $attributes  validated event form data
     * @param  list<string>  $professions
     */
    public function handle(array $attributes, array $professions): Event
    {
        return DB::transaction(function () use ($attributes, $professions): Event {
            $event = new Event($attributes);
            $event->forgetFieldsOutsideFormat();
            $event->slug = $this->generateEventSlug->handle($event);
            $event->save();

            $this->syncEventProfessions->handle($event, $professions);

            return $event;
        });
    }
}
