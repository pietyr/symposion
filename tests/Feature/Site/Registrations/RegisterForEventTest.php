<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->event = Event::factory()->hybrid()->create([
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(11),
        'price_online' => 9900,
        'price_in_person' => 25000,
        'seats_in_person' => 50,
    ]);
    $this->event->professionEntries()->create(['profession' => 'nurse']);
    $this->nurse = User::factory()->create(['profession' => 'nurse']);

    $this->formUrl = route('events.registration.create', ['event' => $this->event, 'slug' => $this->event->slug]);
    $this->storeUrl = route('events.registration.store', ['event' => $this->event, 'slug' => $this->event->slug]);
});

test('a guest is sent to sign in first', function () {
    $this->get($this->formUrl)->assertRedirect(route('login'));
    $this->post($this->storeUrl, ['attendance' => 'online'])->assertRedirect(route('login'));
});

test('an unverified account has to verify the email address first', function () {
    $this->actingAs(User::factory()->unverified()->create(['profession' => 'nurse']))
        ->get($this->formUrl)
        ->assertRedirect(route('verification.notice'));
});

test('the form offers the attendances with prices and free seats', function () {
    Registration::factory()->for($this->event)->create(['attendance' => 'in_person']);

    $this->actingAs($this->nurse)
        ->get($this->formUrl)
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/events/Register')
            ->where('event.id', $this->event->id)
            ->where('attendances', [
                ['value' => 'online', 'label' => 'Online', 'price' => 9900, 'seats_left' => null],
                ['value' => 'in_person', 'label' => 'Stacjonarnie', 'price' => 25000, 'seats_left' => 49],
            ]),
        );
});

test('an outdated slug leads to the current address of the form', function () {
    $this->actingAs($this->nurse)
        ->get(route('events.registration.create', ['event' => $this->event, 'slug' => 'stary']))
        ->assertRedirect($this->formUrl);
});

test('when registration is not possible the form sends back to the event page', function () {
    $this->actingAs(User::factory()->create(['profession' => 'student']))
        ->get($this->formUrl)
        ->assertRedirect(route('events.show', ['event' => $this->event, 'slug' => $this->event->slug]));
});

test('a participant registers at the event price', function () {
    $this->actingAs($this->nurse)
        ->post($this->storeUrl, ['attendance' => 'in_person'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('events.show', ['event' => $this->event, 'slug' => $this->event->slug]));

    expect(Registration::query()->sole())
        ->user_id->toBe($this->nurse->id)
        ->amount->toBe(25000)
        ->status->toBe(RegistrationStatus::Pending);
});

test('a free registration is paid at once', function () {
    $this->event->update(['price_online' => 0]);

    $this->actingAs($this->nurse)->post($this->storeUrl, ['attendance' => 'online']);

    expect(Registration::query()->sole()->status)->toBe(RegistrationStatus::Paid);
});

test('a hidden event reached through its link takes registrations', function () {
    $this->event->update(['is_visible' => false]);

    $this->actingAs($this->nurse)->post($this->storeUrl, ['attendance' => 'online'])->assertSessionHasNoErrors();
});

test('registration is refused when it is not available', function (Closure $setUp) {
    // Returns another participant, or prepares the event and leaves the nurse.
    $user = $setUp($this) ?? $this->nurse;

    $this->actingAs($user)
        ->post($this->storeUrl, ['attendance' => 'online'])
        ->assertSessionHasErrors('attendance');

    expect(Registration::query()->where('user_id', $user->id)->count())->toBeLessThanOrEqual(1);
})->with([
    'not for the profession' => [fn () => User::factory()->create(['profession' => 'student'])],
    'no profession' => [fn () => User::factory()->create()],
    'not open yet' => [function ($test) {
        $test->event->update(['registration_opens_at' => now()->addDay()]);
    }],
    'closed' => [function ($test) {
        $test->event->update(['registration_closes_at' => now()->subDay()]);
    }],
    'already registered' => [function ($test) {
        Registration::factory()->for($test->event)->for($test->nurse)->create();
    }],
]);

test('an attendance the event does not offer is refused', function () {
    $online = Event::factory()->online()->create(['starts_at' => now()->addDays(5), 'ends_at' => now()->addDays(5)]);

    $this->actingAs($this->nurse)
        ->post(route('events.registration.store', ['event' => $online, 'slug' => $online->slug]), ['attendance' => 'in_person'])
        ->assertSessionHasErrors('attendance');
});

test('a full attendance is refused', function () {
    $this->event->update(['seats_in_person' => 1]);
    Registration::factory()->for($this->event)->create(['attendance' => 'in_person']);

    $this->actingAs($this->nurse)
        ->post($this->storeUrl, ['attendance' => 'in_person'])
        ->assertSessionHasErrors('attendance');
});

test('the event page tells whether the visitor may start registering', function () {
    $showUrl = route('events.show', ['event' => $this->event, 'slug' => $this->event->slug]);

    $this->get($showUrl)->assertInertia(fn (Assert $page) => $page->where('registrationAvailability', 'sign_in_required'));
    $this->actingAs($this->nurse)->get($showUrl)->assertInertia(fn (Assert $page) => $page->where('registrationAvailability', 'open'));
});
