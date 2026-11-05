<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;

final class SyncEventProfessions
{
    /**
     * Make the event open to exactly the given professions.
     *
     * @param  list<string>  $professions
     */
    public function handle(Event $event, array $professions): void
    {
        $event->professionEntries()->whereNotIn('profession', $professions)->delete();

        foreach ($professions as $profession) {
            $event->professionEntries()->firstOrCreate(['profession' => $profession]);
        }
    }
}
