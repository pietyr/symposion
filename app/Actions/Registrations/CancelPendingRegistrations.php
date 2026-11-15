<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\RegistrationStatus;
use App\Models\User;

final class CancelPendingRegistrations
{
    /**
     * Cancel the unpaid registrations of an account that can no longer be used, so they stop taking seats (ADR-025).
     */
    public function handle(User $user): void
    {
        $user->registrations()
            ->where('status', RegistrationStatus::Pending)
            ->update(['status' => RegistrationStatus::Cancelled]);
    }
}
