<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;

final class DeleteEvent
{
    /**
     * Move an event to the trash; it disappears from the site but can be restored.
     */
    public function handle(Event $event): void
    {
        $event->delete();
    }
}
