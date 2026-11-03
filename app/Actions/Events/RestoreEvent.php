<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;

final class RestoreEvent
{
    /**
     * Bring a deleted event back from the trash.
     */
    public function handle(Event $event): void
    {
        $event->restore();
    }
}
