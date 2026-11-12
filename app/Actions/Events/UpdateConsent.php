<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Consent;

final class UpdateConsent
{
    public function __construct(private readonly SanitizeEventContent $sanitizeEventContent) {}

    public function handle(Consent $consent, string $content, bool $isRequired): void
    {
        $consent->update([
            'content' => $this->sanitizeEventContent->handle($content),
            'is_required' => $isRequired,
        ]);
    }
}
