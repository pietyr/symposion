<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Validation\ValidationException;

final class ConfirmRegistration
{
    /**
     * Mark a pending registration as paid, e.g. after a bank transfer arrived.
     *
     * @throws ValidationException when the registration is not pending
     */
    public function handle(Registration $registration): void
    {
        if ($registration->status !== RegistrationStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => __('Only a pending registration can be confirmed.'),
            ]);
        }

        $registration->update([
            'status' => RegistrationStatus::Paid,
            'confirmed_at' => now(),
        ]);
    }
}
