<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->admin()->create());

    $this->event = Event::factory()->create();

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

    $this->upload = fn (string $name = 'baner.png', int $kilobytes = 100, string $mime = 'image/png'): UploadedFile => UploadedFile::fake()->create($name, $kilobytes, $mime);

    $this->eventWithBanners = function (): Event {
        Storage::disk('public')->put('events/old-large.png', 'large');
        Storage::disk('public')->put('events/old-small.png', 'small');

        $this->event->update(['large_banner_path' => 'events/old-large.png', 'small_banner_path' => 'events/old-small.png']);

        return $this->event;
    };
});

test('an event is created with banners', function () {
    $this->post(route('admin.events.store'), [
        ...$this->payload,
        'title' => 'Nowe',
        'large_banner' => ($this->upload)(),
        'small_banner' => ($this->upload)('maly.webp', 100, 'image/webp'),
    ])->assertSessionHasNoErrors();

    $event = Event::query()->where('title', 'Nowe')->sole();

    expect($event->large_banner_path)->toStartWith("events/{$event->id}/")->toEndWith('.png')
        ->and($event->small_banner_path)->toEndWith('.webp');

    Storage::disk('public')->assertExists([$event->large_banner_path, $event->small_banner_path]);
});

test('the name of the uploaded file is not used on the disk', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'large_banner' => ($this->upload)('moj baner.png')])
        ->assertSessionHasNoErrors();

    expect($this->event->fresh()->large_banner_path)->not->toContain('moj baner');
});

test('a new banner replaces the old file and leaves the other banner alone', function () {
    $event = ($this->eventWithBanners)();

    $this->put(route('admin.events.update', $event), [...$this->payload, 'large_banner' => ($this->upload)()])
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('events/old-large.png');
    Storage::disk('public')->assertExists(['events/old-small.png', $event->fresh()->large_banner_path]);
    expect($event->fresh()->small_banner_path)->toBe('events/old-small.png');
});

test('saving the form without files keeps the banners', function () {
    $event = ($this->eventWithBanners)();

    $this->put(route('admin.events.update', $event), $this->payload)->assertSessionHasNoErrors();

    expect($event->fresh())
        ->large_banner_path->toBe('events/old-large.png')
        ->small_banner_path->toBe('events/old-small.png');
});

test('a banner can be removed', function () {
    $event = ($this->eventWithBanners)();

    $this->put(route('admin.events.update', $event), [...$this->payload, 'remove_small_banner' => '1'])
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('events/old-small.png');
    Storage::disk('public')->assertExists('events/old-large.png');
    expect($event->fresh()->small_banner_path)->toBeNull();
});

test('a new file wins over removing the banner', function () {
    $event = ($this->eventWithBanners)();

    $this->put(route('admin.events.update', $event), [...$this->payload, 'large_banner' => ($this->upload)(), 'remove_large_banner' => '1'])
        ->assertSessionHasNoErrors();

    expect($event->fresh()->large_banner_path)->toStartWith("events/{$event->id}/");
});

test('the edit form gets the banner addresses', function () {
    $event = ($this->eventWithBanners)();

    $this->get(route('admin.events.edit', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.large_banner_url', Storage::disk('public')->url('events/old-large.png'))
            ->where('event.small_banner_url', Storage::disk('public')->url('events/old-small.png')),
        );
});

test('a banner of exactly 5 MB is accepted', function () {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'large_banner' => ($this->upload)('baner.png', 5 * 1024)])
        ->assertSessionHasNoErrors();
});

test('other files are rejected', function (string $name, int $kilobytes, string $mime) {
    $this->put(route('admin.events.update', $this->event), [...$this->payload, 'small_banner' => ($this->upload)($name, $kilobytes, $mime)])
        ->assertSessionHasErrors('small_banner');

    expect($this->event->fresh()->small_banner_path)->toBeNull();
})->with([
    'too large' => ['baner.png', 5 * 1024 + 1, 'image/png'],
    'svg' => ['baner.svg', 10, 'image/svg+xml'],
    'gif' => ['baner.gif', 10, 'image/gif'],
    'pdf' => ['baner.pdf', 10, 'application/pdf'],
]);
