<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->event = Event::factory()->hybrid()->create();

    $this->payload = [
        'title' => $this->event->title,
        'format' => 'hybrid',
        'voivodeship' => $this->event->voivodeship?->value,
        'city' => $this->event->city,
        'venue' => $this->event->venue,
        'starts_at' => $this->event->starts_at->format('Y-m-d\\TH:i'),
        'ends_at' => $this->event->ends_at->format('Y-m-d\\TH:i'),
        'price_online' => '50',
        'price_in_person' => '100',
        'professions' => ['doctor'],
    ];
});

test('a hybrid event has separate seat limits', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'seats_online' => '500', 'seats_in_person' => '120'])
        ->assertSessionHasNoErrors();

    $this->get(route('admin.events.edit', $this->event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.seats_online', 500)
            ->where('event.seats_in_person', 120),
        );
});

test('an empty limit means no limit', function () {
    $this->event->update(['seats_online' => 10, 'seats_in_person' => 10]);

    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'seats_online' => '', 'seats_in_person' => ''])
        ->assertSessionHasNoErrors();

    expect($this->event->fresh())
        ->seats_online->toBeNull()
        ->seats_in_person->toBeNull();
});

test('the format drops the limit it does not use', function (string $format, ?int $online, ?int $inPerson) {
    $this->put(route('admin.events.update', $this->event), [
        ...$this->payload,
        'format' => $format,
        'seats_online' => '500',
        'seats_in_person' => '120',
    ])->assertSessionHasNoErrors();

    expect($this->event->fresh())
        ->seats_online->toBe($online)
        ->seats_in_person->toBe($inPerson);
})->with([
    'online' => ['online', 500, null],
    'in person' => ['in_person', null, 120],
]);

test('a limit has to be a positive whole number', function (string $value) {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'seats_online' => $value])
        ->assertSessionHasErrors('seats_online');
})->with(['0', '-5', '12.5', 'dużo']);
