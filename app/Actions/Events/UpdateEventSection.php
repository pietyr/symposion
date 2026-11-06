<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\EventSection;

final class UpdateEventSection
{
    public function __construct(private readonly SanitizeEventContent $sanitizeEventContent) {}

    public function handle(EventSection $section, string $title, string $content): void
    {
        $section->update([
            'title' => $title,
            'content' => $this->sanitizeEventContent->handle($content),
        ]);
    }
}
