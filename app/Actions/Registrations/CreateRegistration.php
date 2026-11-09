<?php

declare(strict_types=1);

namespace App\Actions\Registrations;

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateRegistration
{
    /**
     * Register the participant; a free registration is paid straight away (ADR-019).
     *
     * The event row stays locked while seats are counted, so two registrations
     * at the same moment cannot both take the last seat.
     *
     * @throws ValidationException when the participant is already registered or no seat is left
     */
    public function handle(Event $event, User $participant, Attendance $attendance, int $amount): Registration
    {
        return DB::transaction(function () use ($event, $participant, $attendance, $amount): Registration {
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

            $isFree = $amount === 0;

            $registration = $event->registrations()->make([
                'attendance' => $attendance,
                'amount' => $amount,
                'status' => $isFree ? RegistrationStatus::Paid : RegistrationStatus::Pending,
                'confirmed_at' => $isFree ? now() : null,
            ]);
            $registration->user()->associate($participant);
            $registration->save();

            return $registration;
        });
    }
}
