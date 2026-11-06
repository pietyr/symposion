<?php

declare(strict_types=1);

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('the default banner pair follows the event id', function (int $id, int $number) {
    expect((new Event)->forceFill(['id' => $id])->default_banner)->toBe($number);
})->with([
    'first' => [1, 1],
    'last of the pairs' => [9, 9],
    'starts over' => [10, 1],
    'further on' => [24, 6],
]);

test('every default banner has all its sizes and formats', function () {
    $sizes = ['banner' => [480, 768, 1280, 1920, 3840], 'thumbnail' => [480, 768, 1280]];

    foreach ($sizes as $kind => $widths) {
        foreach ($widths as $width) {
            foreach (range(1, 9) as $number) {
                foreach (['avif', 'webp'] as $extension) {
                    expect(public_path("images/event-banners/{$kind}-{$number}-{$width}.{$extension}"))->toBeFile();
                }
            }
        }
    }
});

test('visitors get the uploaded banners and the default pair', function () {
    Storage::fake('public');

    $event = Event::factory()->visible()->create(['small_banner_path' => 'events/1/small.png']);

    $this->get(route('events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('events.data.0.small_banner_url', Storage::disk('public')->url('events/1/small.png'))
            ->where('events.data.0.large_banner_url', null)
            ->where('events.data.0.default_banner', $event->default_banner),
        );
});

test('the event page gets the large banner', function () {
    Storage::fake('public');

    $event = Event::factory()->visible()->create(['large_banner_path' => 'events/1/large.png']);

    $this->get(route('events.show', ['event' => $event, 'slug' => $event->slug]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.large_banner_url', Storage::disk('public')->url('events/1/large.png'))
            ->where('event.default_banner', $event->default_banner),
        );
});
