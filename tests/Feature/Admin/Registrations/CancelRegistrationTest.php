<?php

declare(strict_types=1);

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

test('only administrators cancel registrations', function () {
    $registration = Registration::factory()->create();

    $this->patch(route('admin.registrations.cancel', $registration))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())
        ->patch(route('admin.registrations.cancel', $registration))
        ->assertForbidden();

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Pending);
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('pending and paid registrations can be cancelled', function (string $state) {
        $registration = Registration::factory()->create(['status' => $state]);

        $this->from(route('admin.registrations.show', $registration))
            ->patch(route('admin.registrations.cancel', $registration))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.registrations.show', $registration));

        expect($registration->fresh()->status)->toBe(RegistrationStatus::Cancelled);
    })->with(['pending' => ['pending'], 'paid' => ['paid']]);

    test('cancelling from the participant list goes back to it', function () {
        $registration = Registration::factory()->create();
        $list = route('admin.events.participants.index', $registration->event);

        $this->from($list)
            ->patch(route('admin.registrations.cancel', $registration))
            ->assertRedirect($list);
    });

    test('cancelling frees the seat', function () {
        $event = Event::factory()->create(['seats_in_person' => 1]);
        $registration = Registration::factory()->for($event)->create(['attendance' => Attendance::InPerson]);

        expect($event->seatsLeft(Attendance::InPerson))->toBe(0);

        $this->patch(route('admin.registrations.cancel', $registration));

        expect($event->seatsLeft(Attendance::InPerson))->toBe(1);
    });

    test('a cancelled registration cannot be cancelled again', function () {
        $registration = Registration::factory()->cancelled()->create();

        $this->patch(route('admin.registrations.cancel', $registration))
            ->assertSessionHasErrors('status');
    });
});
