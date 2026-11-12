<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Consent;
use App\Models\Event;

final class CreateConsent
{
    public function __construct(private readonly SanitizeEventContent $sanitizeEventContent) {}

    /**
     * Add a consent at the end of the event's registration form.
     */
    public function handle(Event $event, string $content, bool $isRequired): Consent
    {
        return $event->consents()->create([
            'content' => $this->sanitizeEventContent->handle($content),
            'is_required' => $isRequired,
            'position' => ($event->consents()->reorder('position', 'desc')->first()->position ?? 0) + 1,
        ]);
    }
}
