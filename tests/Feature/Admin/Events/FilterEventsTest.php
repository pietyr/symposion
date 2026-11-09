<?php

declare(strict_types=1);

use App\Enums\Profession;
use App\Enums\Voivodeship;
use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('filters replace the overview with a full list', function () {
    Event::factory()->online()->create();

    $this->get(route('admin.events.index', ['format' => 'online']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('overview', null)
            ->has('events.data', 1)
            ->where('filters.format', 'online'),
        );
});

test('hidden and past events are included, unlike on the site', function () {
    Event::factory()->online()->create();
    Event::factory()->visible()->online()->past()->create();

    $this->get(route('admin.events.index', ['format' => 'online']))
        ->assertInertia(fn (Assert $page) => $page->has('events.data', 2));
});

test('events can be filtered by visibility', function (string $visibility, bool $isVisible) {
    $match = Event::factory()->create(['is_visible' => $isVisible]);
    Event::factory()->create(['is_visible' => ! $isVisible]);

    $this->get(route('admin.events.index', ['visibility' => $visibility]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id)
            ->where('filters.visibility', $visibility),
        );
})->with([
    'visible' => ['visible', true],
    'hidden' => ['hidden', false],
]);

test('filters work with the voivodeship, profession and price', function (array $query) {
    $match = Event::factory()->create(['voivodeship' => Voivodeship::Masovian, 'price_in_person' => 0]);
    $match->professionEntries()->create(['profession' => 'student']);
    Event::factory()->create(['voivodeship' => Voivodeship::Pomeranian, 'price_in_person' => 15000]);

    $this->get(route('admin.events.index', $query))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
})->with([
    'voivodeship' => [['voivodeship' => 'mazowieckie']],
    'profession' => [['profession' => 'student']],
    'price' => [['price' => 'bezplatne']],
]);

test('filters combine with the sort and the deleted events', function () {
    $deleted = Event::factory()->online()->create();
    $deleted->delete();
    Event::factory()->online()->create();
    Event::factory()->create()->delete();

    $this->get(route('admin.events.index', ['format' => 'online', 'trashed' => 1, 'sort' => 'latest']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $deleted->id)
            ->where('filters.sort', 'latest')
            ->where('filters.trashed', true),
        );
});

test('unknown filter values are ignored and keep the overview', function () {
    $this->get(route('admin.events.index', ['format' => 'webinar', 'visibility' => 'maybe']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events', null)
            ->has('overview')
            ->where('filters.format', '')
            ->where('filters.visibility', ''),
        );
});

test('pagination keeps the filters', function () {
    Event::factory()->online()->count(16)->create();

    $this->get(route('admin.events.index', ['format' => 'online']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.links.next', fn (string $url) => str_contains($url, 'format=online')),
        );
});

test('the page offers the filter options', function () {
    $this->get(route('admin.events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('filterOptions.formats', 3)
            ->has('filterOptions.professions', count(Profession::cases()))
            ->has('filterOptions.voivodeships', 16)
            ->has('filterOptions.prices', 2)
            ->has('filterOptions.visibilities', 2),
        );
});
