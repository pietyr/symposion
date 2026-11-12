<?php

declare(strict_types=1);

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

beforeEach(function () {
    $this->participant = User::factory()->create();
});

test('only the participant cancels their registration', function () {
    $registration = Registration::factory()->for($this->participant)->create();

    $this->patch(route('account.registrations.cancel', $registration))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())
        ->patch(route('account.registrations.cancel', $registration))
        ->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('account.registrations.cancel', $registration))
        ->assertForbidden();

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Pending);
});

test('a participant cancels a pending registration, which frees the seat', function () {
    $event = Event::factory()->create(['seats_in_person' => 1]);
    $registration = Registration::factory()->for($this->participant)->for($event)->create(['attendance' => Attendance::InPerson]);

    $this->actingAs($this->participant)
        ->patch(route('account.registrations.cancel', $registration))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account.registrations.index'));

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Cancelled)
        ->and($event->seatsLeft(Attendance::InPerson))->toBe(1);
});

test('paid and cancelled registrations cannot be cancelled by the participant', function (string $state, string $message) {
    $registration = Registration::factory()->for($this->participant)->{$state}()->create();

    $this->actingAs($this->participant)
        ->patch(route('account.registrations.cancel', $registration))
        ->assertSessionHasErrors(['status' => $message]);

    expect($registration->fresh()->status->value)->toBe($state);
})->with([
    'paid' => ['paid', 'Opłacony zapis może anulować tylko organizator.'],
    'cancelled' => ['cancelled', 'Zapis jest już anulowany.'],
]);
