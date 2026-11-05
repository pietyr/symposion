<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the list shows active events, upcoming from the nearest, then past from the most recent', function () {
    $older = Event::factory()->create(['starts_at' => now()->subDays(20), 'ends_at' => now()->subDays(20)]);
    $later = Event::factory()->create(['starts_at' => now()->addDays(20), 'ends_at' => now()->addDays(20)]);
    $recent = Event::factory()->create(['starts_at' => now()->subDays(10), 'ends_at' => now()->subDays(10)]);
    $sooner = Event::factory()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Event::factory()->create()->delete();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/Index')
            ->has('events.data', 4)
            ->where('events.data.0.id', $sooner->id)
            ->where('events.data.0.is_past', false)
            ->where('events.data.1.id', $later->id)
            ->where('events.data.2.id', $recent->id)
            ->where('events.data.2.is_past', true)
            ->where('events.data.3.id', $older->id)
            ->where('filters.trashed', false),
        );
});

test('an event in progress is listed with the upcoming ones', function () {
    $inProgress = Event::factory()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
    Event::factory()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Event::factory()->past()->create();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.data.0.id', $inProgress->id)
            ->where('events.data.0.is_past', false),
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

test('events can be sorted', function (string $sort, array $expectedOrder) {
    $lastWeek = Event::factory()->create(['starts_at' => now()->subDays(7), 'ends_at' => now()->subDays(7), 'created_at' => now()->subDays(1)]);
    $tomorrow = Event::factory()->create(['starts_at' => now()->addDay(), 'ends_at' => now()->addDay(), 'created_at' => now()->subDays(3)]);
    $nextYear = Event::factory()->create(['starts_at' => now()->addYear(), 'ends_at' => now()->addYear(), 'created_at' => now()->subDays(2)]);

    $events = ['lastWeek' => $lastWeek, 'tomorrow' => $tomorrow, 'nextYear' => $nextYear];

    $this->get(route('admin.events.index', ['sort' => $sort]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', $sort)
            ->where('events.data.0.id', $events[$expectedOrder[0]]->id)
            ->where('events.data.1.id', $events[$expectedOrder[1]]->id)
            ->where('events.data.2.id', $events[$expectedOrder[2]]->id),
        );
})->with([
    'upcoming then past' => ['upcoming_then_past', ['tomorrow', 'nextYear', 'lastWeek']],
    'nearest' => ['nearest', ['tomorrow', 'lastWeek', 'nextYear']],
    'latest' => ['latest', ['nextYear', 'tomorrow', 'lastWeek']],
    'recently added' => ['recently_added', ['lastWeek', 'nextYear', 'tomorrow']],
]);

test('an unknown sort falls back to the default order', function () {
    $this->get(route('admin.events.index', ['sort' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'upcoming_then_past')
            ->has('sorts', 4),
        );
});

test('pagination links keep the chosen sort', function () {
    Event::factory()->count(16)->create();

    $this->get(route('admin.events.index', ['sort' => 'latest']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.links.next', fn (string $url) => str_contains($url, 'sort=latest')),
        );
});
