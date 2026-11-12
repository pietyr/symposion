<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Whether the visitor can register for an event right now, and if not, why.
 */
enum RegistrationAvailability: string
{
    case Open = 'open';
    case SignInRequired = 'sign_in_required';
    case NotOpenYet = 'not_open_yet';
    case Closed = 'closed';
    case AlreadyRegistered = 'already_registered';
    case ProfessionMissing = 'profession_missing';
    case ProfessionNotAllowed = 'profession_not_allowed';
    case Full = 'full';

    /**
     * A guest sees the button too; signing in leads back to the registration page.
     */
    public function showsButton(): bool
    {
        return $this === self::Open || $this === self::SignInRequired;
    }

    public function message(): string
    {
        return match ($this) {
            self::Open, self::SignInRequired => '',
            self::NotOpenYet => __('Registration has not opened yet.'),
            self::Closed => __('Registration is closed.'),
            self::AlreadyRegistered => __('You are already registered for this event.'),
            self::ProfessionMissing => __('Add your profession in the profile settings to register.'),
            self::ProfessionNotAllowed => __('This event is not intended for your profession.'),
            self::Full => __('There are no seats left.'),
        };
    }
}
