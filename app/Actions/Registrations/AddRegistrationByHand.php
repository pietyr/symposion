<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Attendance;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class AddRegistrationByHand
{
    public function __construct(
        private ResolveBenefitChoices $resolveBenefitChoices,
        private CreateRegistration $createRegistration,
    ) {}

    /**
     * The administrator sets the amount, but the chosen benefits follow the same rules
     * as in the participant's form, seat limits included (ADR-022). No consents are asked for.
     *
     * @param  array<array-key, mixed>  $benefitChoices  as submitted, see ResolveBenefitChoices
     *
     * @throws ValidationException when a choice is wrong, the participant is already registered or no seat is left
     */
    public function handle(Event $event, User $participant, Attendance $attendance, int $amount, array $benefitChoices): Registration
    {
        $options = $this->resolveBenefitChoices->handle($event, $attendance, $benefitChoices);

        return $this->createRegistration->handle($event, $participant, $attendance, $amount, $options);
    }
}
