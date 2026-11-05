<?php

declare(strict_types=1);

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('the home page shows the nearest published upcoming events', function () {
    $later = Event::factory()->visible()->create(['starts_at' => now()->addDays(20), 'ends_at' => now()->addDays(20)]);
    $sooner = Event::factory()->visible()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Event::factory()->create();
    Event::factory()->visible()->past()->create();
    Event::factory()->visible()->create()->delete();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Home')
            ->has('events', 2)
            ->where('events.0.id', $sooner->id)
            ->where('events.1.id', $later->id),
        );
});

test('the home page shows at most six events', function () {
    Event::factory()->visible()->count(7)->create();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->has('events', 6));
});
