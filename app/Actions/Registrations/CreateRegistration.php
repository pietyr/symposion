<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\BenefitOption;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateRegistration
{
    /**
     * Register the participant with the chosen benefit options; a registration costing nothing
     * is paid straight away (ADR-019, ADR-022).
     *
     * The event row stays locked while seats are counted, so two registrations
     * at the same moment cannot both take the last seat of an attendance or an option.
     *
     * @param  list<BenefitOption>  $options  checked by ResolveBenefitChoices, with their benefit loaded
     *
     * @throws ValidationException when the participant is already registered or no seat is left
     */
    public function handle(Event $event, User $participant, Attendance $attendance, int $amount, array $options = []): Registration
    {
        return DB::transaction(function () use ($event, $participant, $attendance, $amount, $options): Registration {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            if ($event->hasActiveRegistrationOf($participant)) {
                throw ValidationException::withMessages([
                    'user_id' => __('This participant is already registered for the event.'),
                ]);
            }

            if ($event->seatsLeft($attendance) === 0) {
                throw ValidationException::withMessages([
                    'attendance' => __('There are no seats left for this kind of attendance.'),
                ]);
            }

            foreach ($options as $option) {
                if ($option->fresh()?->seatsLeft() === 0) {
                    throw ValidationException::withMessages([
                        "benefits.{$option->benefit_id}" => __('There are no seats left for :option.', ['option' => $option->name]),
                    ]);
                }
            }

            $isFree = $amount === 0;

            $registration = $event->registrations()->make([
                'attendance' => $attendance,
                'amount' => $amount,
                'status' => $isFree ? RegistrationStatus::Paid : RegistrationStatus::Pending,
                'confirmed_at' => $isFree ? now() : null,
            ]);
            $registration->user()->associate($participant);
            $registration->save();

            foreach ($options as $option) {
                $chosen = $registration->benefits()->make([
                    'benefit_name' => $option->benefit->name,
                    'option_name' => $option->name,
                    'price' => $option->price,
                ]);
                $chosen->option()->associate($option);
                $chosen->save();
            }

            return $registration;
        });
    }
}
