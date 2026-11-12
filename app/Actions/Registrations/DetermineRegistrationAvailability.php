<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Profession;
use App\Enums\RegistrationAvailability;
use App\Models\Event;
use App\Models\User;

final class DetermineRegistrationAvailability
{
    /**
     * The reasons are checked from the most general one, so a guest learns that registration
     * is closed before being asked to sign in, and a participant learns about the profession
     * before the seats.
     */
    public function handle(Event $event, ?User $user): RegistrationAvailability
    {
        if ($event->trashed() || $event->registrationClosesAt()->isPast()) {
            return RegistrationAvailability::Closed;
        }

        if ($event->registration_opens_at?->isFuture()) {
            return RegistrationAvailability::NotOpenYet;
        }

        if ($user === null) {
            return RegistrationAvailability::SignInRequired;
        }

        if ($event->hasActiveRegistrationOf($user)) {
            return RegistrationAvailability::AlreadyRegistered;
        }

        $profession = Profession::tryFrom((string) $user->profession);

        if ($profession === null) {
            return RegistrationAvailability::ProfessionMissing;
        }

        if (! $event->isOpenTo($profession)) {
            return RegistrationAvailability::ProfessionNotAllowed;
        }

        foreach ($event->attendances() as $attendance) {
            if ($event->seatsLeft($attendance) !== 0) {
                return RegistrationAvailability::Open;
            }
        }

        return RegistrationAvailability::Full;
    }
}
