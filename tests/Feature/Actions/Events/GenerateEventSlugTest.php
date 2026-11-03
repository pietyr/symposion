<?php

declare(strict_types=1);

use App\Actions\Events\GenerateEventSlug;
use App\Models\Event;

test('the slug combines the title and the city', function () {
    $event = Event::factory()->make(['title' => 'Kongres Kardiologii', 'city' => 'Kraków']);

    expect(app(GenerateEventSlug::class)->handle($event))->toBe('kongres-kardiologii-krakow');
});

test('online events get a slug from the title only', function () {
    $event = Event::factory()->online()->make(['title' => 'Webinar Diabetologii']);

    expect(app(GenerateEventSlug::class)->handle($event))->toBe('webinar-diabetologii');
});

test('events with the same title and city are numbered from two', function () {
    Event::factory()->create(['slug' => 'kongres-kardiologii-krakow']);
    Event::factory()->create(['slug' => 'kongres-kardiologii-krakow-2']);
    Event::factory()->create(['slug' => 'kongres-kardiologii-krakow-pediatria']);

    $event = Event::factory()->make(['title' => 'Kongres Kardiologii', 'city' => 'Kraków']);

    expect(app(GenerateEventSlug::class)->handle($event))->toBe('kongres-kardiologii-krakow-3');
});

test('deleted events still count as taken', function () {
    Event::factory()->create(['slug' => 'kongres-kardiologii-krakow'])->delete();

    $event = Event::factory()->make(['title' => 'Kongres Kardiologii', 'city' => 'Kraków']);

    expect(app(GenerateEventSlug::class)->handle($event))->toBe('kongres-kardiologii-krakow-2');
});

test('an event does not collide with its own slug', function () {
    $event = Event::factory()->create(['title' => 'Kongres Kardiologii', 'city' => 'Kraków', 'slug' => 'kongres-kardiologii-krakow']);

    expect(app(GenerateEventSlug::class)->handle($event))->toBe('kongres-kardiologii-krakow');
});

test('a title without letters or digits still gets a slug', function () {
    $event = Event::factory()->online()->make(['title' => '!!!']);

    expect(app(GenerateEventSlug::class)->handle($event))->toBe('event');
});
