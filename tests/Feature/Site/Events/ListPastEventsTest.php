<?php

declare(strict_types=1);

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('only published past events are listed, most recent first', function () {
    $older = Event::factory()->visible()->create(['starts_at' => now()->subDays(20), 'ends_at' => now()->subDays(20)]);
    $recent = Event::factory()->visible()->create(['starts_at' => now()->subDays(10), 'ends_at' => now()->subDays(10)]);
    Event::factory()->past()->create();
    Event::factory()->visible()->create();
    Event::factory()->visible()->past()->create()->delete();

    $this->get(route('events.past'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/events/Past')
            ->has('events.data', 2)
            ->where('events.data.0.id', $recent->id)
            ->where('events.data.1.id', $older->id),
        );
});

test('an event in progress is not past yet', function () {
    Event::factory()->visible()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);

    $this->get(route('events.past'))
        ->assertInertia(fn (Assert $page) => $page->has('events.data', 0));
});
