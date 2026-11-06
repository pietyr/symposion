<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventSection;
use App\Models\Specialization;
use Inertia\Testing\AssertableInertia as Assert;

test('a published event is shown without administrative fields', function () {
    $event = Event::factory()->visible()->create();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/events/Show')
            ->where('event.id', $event->id)
            ->where('event.title', $event->title)
            ->where('event.price_in_person', $event->price_in_person)
            ->where('event.price_online', null)
            ->missing('event.voivodeship')
            ->missing('event.is_visible')
            ->missing('event.deleted_at'),
        );
});

test('an outdated or missing slug redirects to the current address', function (array $slug) {
    $event = Event::factory()->visible()->create(['slug' => 'kongres-kardiologii-krakow']);

    $this->get(route('events.show', ['event' => $event, ...$slug]))
        ->assertRedirect(route('events.show', ['event' => $event, 'slug' => 'kongres-kardiologii-krakow']))
        ->assertStatus(301);
})->with([
    'outdated' => [['slug' => 'stary-adres']],
    'missing' => [[]],
]);

test('hidden events open through a direct link but stay out of search engines', function () {
    $event = Event::factory()->create();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertHeader('X-Robots-Tag', 'noindex')
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/events/Show')
            ->where('event.id', $event->id),
        );
});

test('published events can be indexed by search engines', function () {
    $event = Event::factory()->visible()->create();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag');
});

test('deleted events do not exist for visitors', function () {
    $event = Event::factory()->visible()->create();
    $event->delete();

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))->assertNotFound();
});

test('a non-numeric id is not found', function () {
    $this->get('/wydarzenie/kongres/kongres')->assertNotFound();
});

test('the event page lists its professions and specializations', function () {
    $event = Event::factory()->visible()->create();
    $event->professionEntries()->createMany([['profession' => 'doctor'], ['profession' => 'retired_profession']]);
    $event->specializations()->attach([
        Specialization::factory()->create(['name' => 'Urologia'])->id,
        Specialization::factory()->create(['name' => 'Alergologia'])->id,
    ]);

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.professions', ['Lekarz'])
            ->where('event.specializations', ['Alergologia', 'Urologia']),
        );
});

test('visitors get the content sections in order', function () {
    $event = Event::factory()->visible()->create();
    EventSection::factory()->for($event)->create(['title' => 'Druga', 'position' => 2, 'content' => '<p>Tekst</p>']);
    EventSection::factory()->for($event)->create(['title' => 'Pierwsza', 'position' => 1]);

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.sections.0.title', 'Pierwsza')
            ->where('event.sections.1.title', 'Druga')
            ->where('event.sections.1.content', '<p>Tekst</p>'),
        );
});
