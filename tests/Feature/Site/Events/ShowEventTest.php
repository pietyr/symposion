<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a published event is shown without administrative fields', function () {
    $event = Event::factory()->published()->create();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/events/Show')
            ->where('event.id', $event->id)
            ->where('event.title', $event->title)
            ->missing('event.voivodeship')
            ->missing('event.is_published')
            ->missing('event.deleted_at'),
        );
});

test('an outdated or missing slug redirects to the current address', function (array $slug) {
    $event = Event::factory()->published()->create(['slug' => 'kongres-kardiologii-krakow']);

    $this->get(route('events.show', ['event' => $event, ...$slug]))
        ->assertRedirect(route('events.show', ['event' => $event, 'slug' => 'kongres-kardiologii-krakow']))
        ->assertStatus(301);
})->with([
    'outdated' => [['slug' => 'stary-adres']],
    'missing' => [[]],
]);

test('hidden events do not exist for visitors', function () {
    $event = Event::factory()->create();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))->assertNotFound();

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertNotFound();
});

test('deleted events do not exist for visitors', function () {
    $event = Event::factory()->published()->create();
    $event->delete();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))->assertNotFound();
});

test('a non-numeric id is not found', function () {
    $this->get('/wydarzenie/kongres/kongres')->assertNotFound();
});
