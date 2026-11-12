<?php

declare(strict_types=1);

use App\Actions\Registrations\DetermineRegistrationAvailability;
use App\Enums\RegistrationAvailability;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

function availabilityOf(Event $event, ?User $user): RegistrationAvailability
{
    return app(DetermineRegistrationAvailability::class)->handle($event, $user);
}

beforeEach(function () {
    $this->event = Event::factory()->visible()->create([
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(11),
        'seats_in_person' => 1,
    ]);
    $this->event->professionEntries()->createMany([['profession' => 'doctor'], ['profession' => 'nurse']]);
    $this->nurse = User::factory()->create(['profession' => 'nurse']);
});

test('a participant of an intended profession can register', function () {
    expect(availabilityOf($this->event, $this->nurse))->toBe(RegistrationAvailability::Open);
});

test('a guest is asked to sign in while registration is open', function () {
    expect(availabilityOf($this->event, null))->toBe(RegistrationAvailability::SignInRequired);
});

test('the registration window decides first', function (Closure $window, RegistrationAvailability $expected) {
    $this->event->update($window());

    expect(availabilityOf($this->event, $this->nurse))->toBe($expected)
        ->and(availabilityOf($this->event, null))->toBe($expected);
})->with([
    'not open yet' => [fn () => ['registration_opens_at' => now()->addDay()], RegistrationAvailability::NotOpenYet],
    'past the closing date' => [fn () => ['registration_closes_at' => now()->subMinute()], RegistrationAvailability::Closed],
    'event already started' => [fn () => ['starts_at' => now()->subHour()], RegistrationAvailability::Closed],
]);

test('an open window with a closing date in the future accepts registrations', function () {
    $this->event->update(['registration_opens_at' => now()->subDay(), 'registration_closes_at' => now()->addDay()]);

    expect(availabilityOf($this->event, $this->nurse))->toBe(RegistrationAvailability::Open);
});

test('a deleted event takes no registrations', function () {
    $this->event->delete();

    expect(availabilityOf($this->event, $this->nurse))->toBe(RegistrationAvailability::Closed);
});

test('the profession has to be one the event is intended for', function () {
    expect(availabilityOf($this->event, User::factory()->create(['profession' => 'student'])))->toBe(RegistrationAvailability::ProfessionNotAllowed)
        ->and(availabilityOf($this->event, User::factory()->create()))->toBe(RegistrationAvailability::ProfessionMissing);
});

test('an event without professions is open to every profession', function () {
    $this->event->professionEntries()->delete();

    expect(availabilityOf($this->event, User::factory()->create(['profession' => 'student'])))->toBe(RegistrationAvailability::Open);
});

test('an active registration counts, a cancelled one does not', function () {
    $registration = Registration::factory()->for($this->event)->for($this->nurse)->create();

    expect(availabilityOf($this->event, $this->nurse))->toBe(RegistrationAvailability::AlreadyRegistered);

    $registration->update(['status' => 'cancelled']);

    expect(availabilityOf($this->event, $this->nurse))->toBe(RegistrationAvailability::Open);
});

test('an event with no seats left in any attendance is full', function () {
    Registration::factory()->for($this->event)->create(['attendance' => 'in_person']);

    expect(availabilityOf($this->event, $this->nurse))->toBe(RegistrationAvailability::Full);
});
