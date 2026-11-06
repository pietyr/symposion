<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventSection;

final class CreateEventSection
{
    public function __construct(private readonly SanitizeEventContent $sanitizeEventContent) {}

    /**
     * Add a section at the end of the event page.
     */
    public function handle(Event $event, string $title, string $content): EventSection
    {
        return $event->sections()->create([
            'title' => $title,
            'content' => $this->sanitizeEventContent->handle($content),
            'position' => ($event->sections()->reorder('position', 'desc')->first()->position ?? 0) + 1,
        ]);
    }
}
