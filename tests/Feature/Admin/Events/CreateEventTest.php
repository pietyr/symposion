<?php

declare(strict_types=1);

use App\Enums\EventFormat;
use App\Enums\Voivodeship;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->payload = [
        'title' => 'Ogólnopolski Kongres Kardiologii',
        'format' => EventFormat::InPerson->value,
        'voivodeship' => Voivodeship::Masovian->value,
        'city' => 'Warszawa',
        'venue' => 'Hotel Marriott, Al. Jerozolimskie 65/79',
        'starts_at' => '2027-03-12T09:00',
        'ends_at' => '2027-03-13T17:00',
        'is_visible' => '1',
    ];
});

test('the create form lists formats and voivodeships', function () {
    $this->get(route('admin.events.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/Create')
            ->has('formats', count(EventFormat::cases()))
            ->has('voivodeships', count(Voivodeship::cases())),
        );
});

test('an event can be created', function () {
    $this->post(route('admin.events.store'), $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.index'));

    $event = Event::sole();

    expect($event)
        ->title->toBe('Ogólnopolski Kongres Kardiologii')
        ->slug->toBe('ogolnopolski-kongres-kardiologii-warszawa')
        ->format->toBe(EventFormat::InPerson)
        ->voivodeship->toBe(Voivodeship::Masovian)
        ->city->toBe('Warszawa')
        ->is_visible->toBeTrue()
        ->and($event->starts_at->format('Y-m-d H:i'))->toBe('2027-03-12 09:00');
});

test('an unchecked publish box creates a hidden event', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'is_visible' => null]);

    expect(Event::sole()->is_visible)->toBeFalse();
});

test('online events are stored without a venue', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'format' => EventFormat::Online->value])
        ->assertSessionHasNoErrors();

    expect(Event::sole())
        ->voivodeship->toBeNull()
        ->city->toBeNull()
        ->venue->toBeNull()
        ->slug->toBe('ogolnopolski-kongres-kardiologii');
});

test('a submitted slug is ignored', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'slug' => 'wlasny-adres']);

    expect(Event::sole()->slug)->toBe('ogolnopolski-kongres-kardiologii-warszawa');
});

test('invalid event data is rejected', function (array $overrides, string $field) {
    $this->post(route('admin.events.store'), [...$this->payload, ...$overrides])
        ->assertSessionHasErrors($field);

    expect(Event::count())->toBe(0);
})->with([
    'missing title' => [['title' => ''], 'title'],
    'unknown format' => [['format' => 'webinar'], 'format'],
    'in person without voivodeship' => [['voivodeship' => null], 'voivodeship'],
    'hybrid without city' => [['format' => EventFormat::Hybrid->value, 'city' => ''], 'city'],
    'in person without venue' => [['venue' => ''], 'venue'],
    'unknown voivodeship' => [['voivodeship' => 'bawaria'], 'voivodeship'],
    'end before start' => [['ends_at' => '2027-03-11T09:00'], 'ends_at'],
    'invalid start date' => [['starts_at' => 'jutro'], 'starts_at'],
]);
