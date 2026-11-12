<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Validation\ValidationException;

final readonly class CancelOwnRegistration
{
    public function __construct(private CancelRegistration $cancelRegistration) {}

    /**
     * A participant may only withdraw a registration that is not paid yet (ADR-020);
     * a paid one involves a refund, which the administrator handles.
     *
     * @throws ValidationException when the registration is paid or already cancelled
     */
    public function handle(Registration $registration): void
    {
        if ($registration->status === RegistrationStatus::Paid) {
            throw ValidationException::withMessages([
                'status' => __('Only the organizer can cancel a paid registration.'),
            ]);
        }

        $this->cancelRegistration->handle($registration);
    }
}
