<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Attendance;
use App\Enums\RegistrationAvailability;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class RegisterForEvent
{
    public function __construct(
        private readonly DetermineRegistrationAvailability $determineRegistrationAvailability,
        private readonly CreateRegistration $createRegistration,
    ) {}

    /**
     * A participant registers themselves at the event price; the dates, the profession
     * and the seats are checked here, unlike when the admin adds a registration by hand.
     *
     * @throws ValidationException when registration is not available or the attendance is not offered
     */
    public function handle(Event $event, User $participant, Attendance $attendance): Registration
    {
        $availability = $this->determineRegistrationAvailability->handle($event, $participant);

        if ($availability !== RegistrationAvailability::Open) {
            throw ValidationException::withMessages(['attendance' => $availability->message()]);
        }

        $price = $event->priceFor($attendance);

        if ($price === null) {
            throw ValidationException::withMessages([
                'attendance' => __('The event does not offer this kind of attendance.'),
            ]);
        }

        return $this->createRegistration->handle($event, $participant, $attendance, $price);
    }
}
