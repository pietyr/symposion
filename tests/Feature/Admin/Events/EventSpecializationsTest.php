<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Specialization;
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
        'professions' => ['doctor'],
    ];
});

test('the forms offer specializations alphabetically', function () {
    Specialization::factory()->create(['name' => 'Urologia']);
    $first = Specialization::factory()->create(['name' => 'Alergologia']);

    $this->get(route('admin.events.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('specializations', 2)
            ->where('specializations.0', ['value' => (string) $first->id, 'label' => 'Alergologia']),
        );
});

test('specializations are optional', function () {
    $this->put(route('admin.events.update', $this->event), $this->payload)
        ->assertSessionHasNoErrors();

    expect($this->event->specializations()->count())->toBe(0);
});

test('a new event stores the chosen specializations', function () {
    $specialization = Specialization::factory()->create();

    $this->post(route('admin.events.store'), [...$this->payload, 'specializations' => [(string) $specialization->id]])
        ->assertSessionHasNoErrors();

    expect(Event::query()->latest('id')->first()?->specializations->pluck('id')->all())
        ->toBe([$specialization->id]);
});

test('updating an event replaces its specializations', function () {
    [$old, $new] = Specialization::factory()->count(2)->create();
    $this->event->specializations()->attach($old);

    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'specializations' => [(string) $new->id]])
        ->assertSessionHasNoErrors();

    expect($this->event->specializations()->pluck('specializations.id')->all())->toBe([$new->id]);
});

test('the edit form receives the event specializations', function () {
    $specialization = Specialization::factory()->create();
    $this->event->specializations()->attach($specialization);

    $this->get(route('admin.events.edit', $this->event))
        ->assertInertia(fn (Assert $page) => $page->where('event.specializations', [(string) $specialization->id]));
});

test('an unknown specialization is rejected', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'specializations' => ['999999']])
        ->assertSessionHasErrors('specializations.0');
});

test('deleting a specialization keeps the events that used it', function () {
    $specialization = Specialization::factory()->create();
    $this->event->specializations()->attach($specialization);

    $this->delete(route('admin.specializations.destroy', $specialization));

    expect($this->event->fresh())->not->toBeNull()
        ->and($this->event->specializations()->count())->toBe(0);
});
