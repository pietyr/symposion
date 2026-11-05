<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

final class UpdateEvent
{
    public function __construct(
        private readonly GenerateEventSlug $generateEventSlug,
        private readonly SyncEventProfessions $syncEventProfessions,
    ) {}

    /**
     * Update an event; a new title or city gives it a new slug, which is safe
     * because public URLs identify the event by its id.
     *
     * @param  array<string, mixed>  $attributes  validated event form data
     * @param  list<string>  $professions
     * @param  list<int>  $specializations
     */
    public function handle(Event $event, array $attributes, array $professions, array $specializations): Event
    {
        return DB::transaction(function () use ($event, $attributes, $professions, $specializations): Event {
            $event->fill($attributes);
            $event->forgetFieldsOutsideFormat();

            if ($event->isDirty(['title', 'city'])) {
                $event->slug = $this->generateEventSlug->handle($event);
            }

            $event->save();

            $this->syncEventProfessions->handle($event, $professions);
            $event->specializations()->sync($specializations);

            return $event;
        });
    }
}
