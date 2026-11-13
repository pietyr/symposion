<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Attendance;
use App\Enums\RegistrationAvailability;
use App\Mail\RegistrationConfirmation;
use App\Models\BenefitOption;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class RegisterForEvent
{
    public function __construct(
        private readonly DetermineRegistrationAvailability $determineRegistrationAvailability,
        private readonly CreateRegistration $createRegistration,
        private readonly ResolveBenefitChoices $resolveBenefitChoices,
    ) {}

    /**
     * A participant registers themselves at the event price; the dates, the profession
     * and the seats are checked here, unlike when the admin adds a registration by hand,
     * and only here the participant gets a confirmation email.
     *
     * The amount is the attendance price plus the chosen benefit options.
     *
     * @param  array<array-key, mixed>  $benefitChoices  as submitted, see ResolveBenefitChoices
     *
     * @throws ValidationException when registration is not available, the attendance is not offered or a choice is wrong
     */
    public function handle(Event $event, User $participant, Attendance $attendance, array $benefitChoices = []): Registration
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

        $options = $this->resolveBenefitChoices->handle($event, $attendance, $benefitChoices);
        $amount = $price + array_sum(array_map(fn (BenefitOption $option): int => $option->price, $options));

        $registration = $this->createRegistration->handle($event, $participant, $attendance, $amount, $options);
        $registration->setRelation('event', $event);

        Mail::to($participant)->queue(new RegistrationConfirmation($registration));

        return $registration;
    }
}
