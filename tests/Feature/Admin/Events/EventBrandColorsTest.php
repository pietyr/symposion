<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->event = Event::factory()->create(['primary_color' => '#0f766e', 'accent_color' => '#f59e0b']);

    $this->payload = [
        'title' => $this->event->title,
        'format' => $this->event->format->value,
        'voivodeship' => $this->event->voivodeship?->value,
        'city' => $this->event->city,
        'venue' => $this->event->venue,
        'starts_at' => $this->event->starts_at->format('Y-m-d\\TH:i'),
        'ends_at' => $this->event->ends_at->format('Y-m-d\\TH:i'),
        'price_in_person' => '100',
        'professions' => ['doctor'],
    ];
});

test('an event is created with brand colors', function () {
    $this->post(route('admin.events.store'), [...$this->payload, 'title' => 'Nowe', 'primary_color' => '#1D4ED8', 'accent_color' => '#f97316'])
        ->assertSessionHasNoErrors();

    expect(Event::query()->where('title', 'Nowe')->sole())
        ->primary_color->toBe('#1D4ED8')
        ->accent_color->toBe('#f97316');
});

test('brand colors can be changed and cleared', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'primary_color' => '#1d4ed8', 'accent_color' => ''])
        ->assertSessionHasNoErrors();

    expect($this->event->fresh())
        ->primary_color->toBe('#1d4ed8')
        ->accent_color->toBeNull();
});

test('the edit form gets the saved colors', function () {
    $this->get(route('admin.events.edit', $this->event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.primary_color', '#0f766e')
            ->where('event.accent_color', '#f59e0b'),
        );
});

test('only full hex colors are accepted', function (string $field, string $color) {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, $field => $color])
        ->assertSessionHasErrors($field);
})->with([
    'no hash' => ['primary_color', '0f766e'],
    'short form' => ['primary_color', '#abc'],
    'with alpha' => ['accent_color', '#0f766eff'],
    'not hex' => ['accent_color', '#gggggg'],
    'css injection' => ['accent_color', '#000000;}'],
]);
