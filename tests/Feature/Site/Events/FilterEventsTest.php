<?php

declare(strict_types=1);

use App\Enums\Voivodeship;
use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

function listedEventIds(Assert $page): array
{
    return collect($page->toArray()['props']['events']['data'])->pluck('id')->sort()->values()->all();
}

test('events can be filtered by format', function () {
    $online = Event::factory()->visible()->online()->create();
    Event::factory()->visible()->create();

    $this->get(route('events.index', ['forma' => 'online']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $online->id)
            ->where('filters.format', 'online'),
        );
});

test('events can be filtered by voivodeship', function () {
    $match = Event::factory()->visible()->create(['voivodeship' => Voivodeship::Masovian]);
    Event::factory()->visible()->create(['voivodeship' => Voivodeship::Pomeranian]);

    $this->get(route('events.index', ['wojewodztwo' => 'mazowieckie']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
});

test('events can be filtered by profession', function () {
    $match = Event::factory()->visible()->create();
    $match->professionEntries()->create(['profession' => 'student']);
    Event::factory()->visible()->create()->professionEntries()->create(['profession' => 'doctor']);

    $this->get(route('events.index', ['zawod' => 'student']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
});

test('a hybrid event free online counts as both free and paid', function (string $price, array $expected) {
    $free = Event::factory()->visible()->create(['price_in_person' => 0]);
    $paid = Event::factory()->visible()->create(['price_in_person' => 15000]);
    $hybrid = Event::factory()->visible()->hybrid()->create(['price_online' => 0, 'price_in_person' => 15000]);
    $ids = ['free' => $free->id, 'paid' => $paid->id, 'hybrid' => $hybrid->id];

    $this->get(route('events.index', ['cena' => $price]))
        ->assertInertia(fn (Assert $page) => expect(listedEventIds($page))
            ->toEqualCanonicalizing(array_map(fn (string $key) => $ids[$key], $expected)));
})->with([
    'free' => ['bezplatne', ['free', 'hybrid']],
    'paid' => ['platne', ['paid', 'hybrid']],
]);

test('events can be searched by title and city', function () {
    $match = Event::factory()->visible()->create(['title' => 'Forum Onkologiczne', 'city' => 'Łódź']);
    Event::factory()->visible()->create(['title' => 'Forum Onkologiczne', 'city' => 'Gdańsk']);

    $this->get(route('events.index', ['szukaj' => 'forum lodz']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id)
            ->where('filters.search', 'forum lodz'),
        );
});

test('filters combine', function () {
    $match = Event::factory()->visible()->online()->create(['price_online' => 0]);
    Event::factory()->visible()->online()->create(['price_online' => 15000]);
    Event::factory()->visible()->create(['price_in_person' => 0]);

    $this->get(route('events.index', ['forma' => 'online', 'cena' => 'bezplatne']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
});

test('unknown filter values are ignored', function () {
    Event::factory()->visible()->count(2)->create();

    $this->get(route('events.index', ['forma' => 'webinar', 'wojewodztwo' => 'bawaria', 'zawod' => 'astronauta', 'cena' => 'gratis']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 2)
            ->where('filters.format', '')
            ->where('filters.voivodeship', '')
            ->where('filters.profession', '')
            ->where('filters.price', ''),
        );
});

test('filters never reveal hidden, past or deleted events', function () {
    Event::factory()->online()->create();
    Event::factory()->visible()->online()->past()->create();
    Event::factory()->visible()->online()->create()->delete();

    $this->get(route('events.index', ['forma' => 'online']))
        ->assertInertia(fn (Assert $page) => $page->has('events.data', 0));
});

test('the past events list is filtered too', function () {
    $match = Event::factory()->visible()->online()->past()->create();
    Event::factory()->visible()->past()->create();
    Event::factory()->visible()->online()->create();

    $this->get(route('events.past', ['forma' => 'online']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('events.data', 1)
            ->where('events.data.0.id', $match->id),
        );
});

test('pagination keeps the filters', function () {
    Event::factory()->visible()->online()->count(13)->create();

    $this->get(route('events.index', ['forma' => 'online']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.links.next', fn (string $url) => str_contains($url, 'forma=online')),
        );
});

test('the page offers the filter options', function () {
    $this->get(route('events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('filterOptions.formats', 3)
            ->has('filterOptions.professions', 9)
            ->has('filterOptions.voivodeships', 16)
            ->has('filterOptions.prices', 2),
        );
});
