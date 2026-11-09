<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->event = Event::factory()->create([
        'starts_at' => '2030-05-10 09:00',
        'ends_at' => '2030-05-11 17:00',
    ]);

    $this->payload = [
        'title' => $this->event->title,
        'format' => $this->event->format->value,
        'voivodeship' => $this->event->voivodeship?->value,
        'city' => $this->event->city,
        'venue' => $this->event->venue,
        'starts_at' => '2030-05-10T09:00',
        'ends_at' => '2030-05-11T17:00',
        'price_in_person' => '100',
        'professions' => ['doctor'],
    ];
});

test('the registration window is saved and shown in the form', function () {
    $this->put(route('admin.events.update', $this->event), [
        ...$this->payload,
        'registration_opens_at' => '2030-03-01T08:00',
        'registration_closes_at' => '2030-05-09T23:59',
    ])->assertSessionHasNoErrors();

    $this->get(route('admin.events.edit', $this->event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.registration_opens_at', fn (string $date) => str_starts_with($date, '2030-03-01T08:00'))
            ->where('event.registration_closes_at', fn (string $date) => str_starts_with($date, '2030-05-09T23:59')),
        );
});

test('both dates are optional', function () {
    $this->event->update(['registration_opens_at' => '2030-03-01 08:00']);

    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'registration_opens_at' => '', 'registration_closes_at' => ''])
        ->assertSessionHasNoErrors();

    expect($this->event->fresh())
        ->registration_opens_at->toBeNull()
        ->registration_closes_at->toBeNull();
});

test('the registration window must make sense', function (array $dates, string $field) {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, ...$dates])
        ->assertSessionHasErrors($field);
})->with([
    'closes before it opens' => [['registration_opens_at' => '2030-04-01T08:00', 'registration_closes_at' => '2030-03-01T08:00'], 'registration_closes_at'],
    'closes after the event' => [['registration_closes_at' => '2030-05-12T08:00'], 'registration_closes_at'],
    'opens after the event' => [['registration_opens_at' => '2030-05-12T08:00'], 'registration_opens_at'],
]);
