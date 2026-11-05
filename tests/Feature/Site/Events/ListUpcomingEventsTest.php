<?php

declare(strict_types=1);

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('only published upcoming events are listed, nearest first', function () {
    $later = Event::factory()->visible()->create(['starts_at' => now()->addDays(20), 'ends_at' => now()->addDays(20)]);
    $sooner = Event::factory()->visible()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Event::factory()->create();
    Event::factory()->visible()->past()->create();
    Event::factory()->visible()->create()->delete();

    $this->get(route('events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/events/Index')
            ->has('events.data', 2)
            ->where('events.data.0.id', $sooner->id)
            ->where('events.data.1.id', $later->id),
        );
});

test('an event in progress is still upcoming', function () {
    $event = Event::factory()->visible()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);

    $this->get(route('events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $event->id),
        );
});

test('the list is paginated', function () {
    Event::factory()->visible()->count(13)->create();

    $this->get(route('events.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.meta.total', 13),
        );
});
