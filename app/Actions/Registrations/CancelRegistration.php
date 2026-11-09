<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Validation\ValidationException;

final class CancelRegistration
{
    /**
     * Cancel a registration, which frees its seat; a refund of a paid one happens outside the system.
     *
     * @throws ValidationException when the registration is already cancelled
     */
    public function handle(Registration $registration): void
    {
        if ($registration->status === RegistrationStatus::Cancelled) {
            throw ValidationException::withMessages([
                'status' => __('The registration is already cancelled.'),
            ]);
        }

        $registration->update(['status' => RegistrationStatus::Cancelled]);
    }
}
