<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Consent;
use Illuminate\Validation\ValidationException;

final class DeleteConsent
{
    /**
     * A consent given in registrations stays as evidence (ADR-022).
     *
     * @throws ValidationException when a registration includes the consent
     */
    public function handle(Consent $consent): void
    {
        if ($consent->registrationConsents()->exists()) {
            throw ValidationException::withMessages([
                'consent' => __('Participants have given this consent, so it cannot be deleted.'),
            ]);
        }

        $consent->delete();
    }
}
