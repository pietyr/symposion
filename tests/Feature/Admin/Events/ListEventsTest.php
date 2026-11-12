<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the overview shows the nearest upcoming and the most recently finished events', function () {
    $older = Event::factory()->create(['starts_at' => now()->subDays(20), 'ends_at' => now()->subDays(20)]);
    $later = Event::factory()->create(['starts_at' => now()->addDays(20), 'ends_at' => now()->addDays(20)]);
    $recent = Event::factory()->create(['starts_at' => now()->subDays(10), 'ends_at' => now()->subDays(10)]);
    $sooner = Event::factory()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Event::factory()->create()->delete();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/Index')
            ->where('events', null)
            ->has('overview.upcoming', 2)
            ->where('overview.upcoming.0.id', $sooner->id)
            ->where('overview.upcoming.1.id', $later->id)
            ->has('overview.past', 2)
            ->where('overview.past.0.id', $recent->id)
            ->where('overview.past.1.id', $older->id)
            ->where('filters.trashed', false),
        );
});

test('the list counts the pending and paid registrations of each event', function () {
    $event = Event::factory()->create();
    Registration::factory()->for($event)->create();
    Registration::factory()->for($event)->paid()->create();
    Registration::factory()->for($event)->cancelled()->create();
    $deleted = Event::factory()->create();
    Registration::factory()->for($deleted)->create();
    $deleted->delete();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page->where('overview.upcoming.0.registrations_count', 2));
    $this->get(route('admin.events.index', ['sort' => 'upcoming']))
        ->assertInertia(fn (Assert $page) => $page->where('events.data.0.registrations_count', 2));
    $this->get(route('admin.events.index', ['trashed' => 1]))
        ->assertInertia(fn (Assert $page) => $page->where('events.data.0.registrations_count', 1));
});

test('the overview is limited to five events on each side', function () {
    Event::factory()->count(6)->create();
    Event::factory()->past()->count(6)->create();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('overview.upcoming', 5)
            ->has('overview.past', 5),
        );
});

test('an event in progress is listed with the upcoming ones', function () {
    $inProgress = Event::factory()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
    Event::factory()->create(['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)]);
    Event::factory()->past()->create();

    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview.upcoming.0.id', $inProgress->id)
            ->where('overview.upcoming.0.is_past', false),
        );
});

test('search results group upcoming events before past ones', function () {
    $past = Event::factory()->past()->create(['title' => 'Kongres Kardiologii A']);
    $upcoming = Event::factory()->create(['title' => 'Kongres Kardiologii B']);

    $this->get(route('admin.events.index', ['search' => 'kongres']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview', null)
            ->where('events.data.0.id', $upcoming->id)
            ->where('events.data.1.id', $past->id),
        );
});

test('choosing upcoming or past lists only those events, paginated', function (string $sort, array $listed, array $other) {
    Event::factory()->state($listed)->count(16)->create();
    Event::factory()->state($other)->count(3)->create();

    $this->get(route('admin.events.index', ['sort' => $sort]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview', null)
            ->has('events.data', 15)
            ->where('events.meta.total', 16)
            ->where('filters.sort', $sort),
        );
})->with(function () {
    $upcoming = ['starts_at' => now()->addDays(10), 'ends_at' => now()->addDays(10)];
    $past = ['starts_at' => now()->subDays(10), 'ends_at' => now()->subDays(10)];

    return [
        'upcoming' => ['upcoming', $upcoming, $past],
        'past' => ['past', $past, $upcoming],
    ];
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

test('search ignores case and Polish characters', function (string $term) {
    $match = Event::factory()->create(['title' => 'Świętokrzyskie Spotkanie Łódzkich Żółtych Ról']);
    Event::factory()->create(['title' => 'Warsztaty Pediatryczne']);

    $this->get(route('admin.events.index', ['search' => $term]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
})->with(['swietokrzyskie', 'ŚWIĘTOKRZYSKIE', 'lodzkich', 'Łódzkich', 'ŁÓDZKICH', 'zoltych', 'rol']);

test('search matches every word, in any order and as fragments', function () {
    $match = Event::factory()->create(['title' => 'IV Małopolski Kongres Kardiologiczny']);
    Event::factory()->create(['title' => 'Małopolskie Warsztaty Onkologiczne']);
    Event::factory()->create(['title' => 'Pomorski Kongres Kardiologiczny']);

    $this->get(route('admin.events.index', ['search' => '  kard  malop ']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
});

test('search also looks in the city, ignoring Polish characters', function (string $term) {
    $match = Event::factory()->create(['title' => 'Forum Onkologiczne', 'city' => 'Łódź']);
    Event::factory()->create(['title' => 'Forum Onkologiczne', 'city' => 'Gdańsk']);
    Event::factory()->online()->create(['title' => 'Forum Onkologiczne']);

    $this->get(route('admin.events.index', ['search' => $term]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
})->with(['lodz', 'Łódź', 'forum lodz', 'lodz onkolog']);

test('search terms are matched literally', function () {
    Event::factory()->create(['title' => 'Kongres Kardiologii 2027']);

    $this->get(route('admin.events.index', ['search' => '%']))
        ->assertInertia(fn (Assert $page) => $page->has('events.data', 0));
});

test('the list is paginated', function () {
    Event::factory()->count(16)->create();

    $this->get(route('admin.events.index', ['sort' => 'latest', 'page' => 2]))
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
    'nearest' => ['nearest', ['tomorrow', 'lastWeek', 'nextYear']],
    'latest' => ['latest', ['nextYear', 'tomorrow', 'lastWeek']],
    'recently added' => ['recently_added', ['lastWeek', 'nextYear', 'tomorrow']],
]);

test('an unknown sort falls back to the default order', function () {
    $this->get(route('admin.events.index', ['sort' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'upcoming_then_past')
            ->has('sorts', 6),
        );
});

test('pagination links keep the chosen sort', function () {
    Event::factory()->count(16)->create();

    $this->get(route('admin.events.index', ['sort' => 'latest']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.links.next', fn (string $url) => str_contains($url, 'sort=latest')),
        );
});
