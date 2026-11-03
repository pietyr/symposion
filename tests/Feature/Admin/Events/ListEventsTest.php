<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the list shows active events with the latest start date first', function () {
    $earlier = Event::factory()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    $later = Event::factory()->create(['starts_at' => now()->addDays(20), 'ends_at' => now()->addDays(20)]);
    Event::factory()->create()->delete();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/Index')
            ->has('events.data', 2)
            ->where('events.data.0.id', $later->id)
            ->where('events.data.1.id', $earlier->id)
            ->where('filters.trashed', false),
        );
});

test('deleted events are listed on request', function () {
    Event::factory()->create();
    $deleted = Event::factory()->create();
    $deleted->delete();

    $this->get(route('admin.events.index', ['trashed' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $deleted->id)
            ->where('filters.trashed', true),
        );
});

test('events can be searched by title', function () {
    $match = Event::factory()->create(['title' => 'Kongres Kardiologii 2027']);
    Event::factory()->create(['title' => 'Warsztaty Pediatrii 2027']);

    $this->get(route('admin.events.index', ['search' => 'kardio']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id)
            ->where('filters.search', 'kardio'),
        );
});

test('search terms are matched literally', function () {
    Event::factory()->create(['title' => 'Kongres Kardiologii 2027']);

    $this->get(route('admin.events.index', ['search' => '%']))
        ->assertInertia(fn (Assert $page) => $page->has('events.data', 0));
});

test('the list is paginated', function () {
    Event::factory()->count(16)->create();

    $this->get(route('admin.events.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.meta.total', 16)
            ->where('events.meta.current_page', 2),
        );
});
