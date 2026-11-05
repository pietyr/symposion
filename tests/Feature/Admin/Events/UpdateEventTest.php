<?php

declare(strict_types=1);

use App\Enums\EventFormat;
use App\Enums\Profession;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->event = Event::factory()->create([
        'title' => 'Kongres Kardiologii',
        'city' => 'Kraków',
        'slug' => 'kongres-kardiologii-krakow',
    ]);

    $this->payload = [
        'title' => 'Kongres Kardiologii',
        'format' => $this->event->format->value,
        'voivodeship' => $this->event->voivodeship?->value,
        'city' => 'Kraków',
        'venue' => $this->event->venue,
        'starts_at' => $this->event->starts_at->format('Y-m-d\TH:i'),
        'ends_at' => $this->event->ends_at->format('Y-m-d\TH:i'),
        'price_in_person' => '100,00',
        'professions' => [Profession::Doctor->value],
    ];
});

test('the edit form shows the event', function () {
    $this->get(route('admin.events.edit', $this->event))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/Edit')
            ->where('event.id', $this->event->id)
            ->where('event.title', 'Kongres Kardiologii')
            ->has('formats')
            ->has('voivodeships'),
        );
});

test('deleted events cannot be edited', function () {
    $this->event->delete();

    $this->get(route('admin.events.edit', $this->event))->assertNotFound();
});

test('an event can be updated', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'venue' => 'ICE Kraków'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.edit', $this->event));

    expect($this->event->fresh())
        ->venue->toBe('ICE Kraków')
        ->slug->toBe('kongres-kardiologii-krakow')
        ->is_visible->toBeTrue();
});

test('a new title or city gives the event a new slug', function (array $overrides, string $slug) {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, ...$overrides]);

    expect($this->event->fresh()->slug)->toBe($slug);
})->with([
    'title' => [['title' => 'Kongres Pediatrii'], 'kongres-pediatrii-krakow'],
    'city' => [['city' => 'Gdańsk'], 'kongres-kardiologii-gdansk'],
]);

test('an event can be hidden on the lists', function () {
    $this->event->forceFill(['is_visible' => true])->save();

    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'is_hidden' => '1']);

    expect($this->event->fresh()->is_visible)->toBeFalse();
});

test('promotion can be switched off', function () {
    $this->event->forceFill(['is_promoted' => true])->save();

    $this->put(route('admin.events.update', $this->event), $this->payload);

    expect($this->event->fresh()->is_promoted)->toBeFalse();
});

test('switching an event to online removes its venue', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'format' => EventFormat::Online->value, 'price_online' => '50']);

    expect($this->event->fresh())
        ->format->toBe(EventFormat::Online)
        ->city->toBeNull()
        ->venue->toBeNull()
        ->slug->toBe('kongres-kardiologii')
        ->price_online->toBe(5000)
        ->price_in_person->toBeNull();
});
