<?php

declare(strict_types=1);

use App\Enums\Profession;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->event = Event::factory()->create();

    $this->payload = [
        'title' => $this->event->title,
        'format' => $this->event->format->value,
        'voivodeship' => $this->event->voivodeship?->value,
        'city' => $this->event->city,
        'venue' => $this->event->venue,
        'starts_at' => $this->event->starts_at->format('Y-m-d\\TH:i'),
        'ends_at' => $this->event->ends_at->format('Y-m-d\\TH:i'),
        'price_in_person' => '100',
    ];
});

test('a new event stores the chosen professions', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'professions' => ['doctor', 'student']])
        ->assertSessionHasNoErrors();

    expect(Event::query()->latest('id')->first()?->professions)
        ->toEqualCanonicalizing([Profession::Doctor, Profession::Student]);
});

test('updating an event replaces its professions', function () {
    $this->event->professionEntries()->createMany([['profession' => 'doctor'], ['profession' => 'paramedic']]);

    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'professions' => ['paramedic', 'student']])
        ->assertSessionHasNoErrors();

    expect($this->event->fresh()->professions)
        ->toEqualCanonicalizing([Profession::Paramedic, Profession::Student]);
});

test('the edit form receives the event professions', function () {
    $this->event->professionEntries()->create(['profession' => 'doctor']);

    $this->get(route('admin.events.edit', $this->event))
        ->assertInertia(fn (Assert $page) => $page->where('event.professions', ['doctor']));
});

test('a stored profession the enum does not know is skipped instead of failing', function () {
    $this->event->professionEntries()->createMany([['profession' => 'doctor'], ['profession' => 'retired_profession']]);

    $this->get(route('admin.events.edit', $this->event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('event.professions', ['doctor']));
});
