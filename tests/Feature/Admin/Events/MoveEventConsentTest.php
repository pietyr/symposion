<?php

declare(strict_types=1);

use App\Models\Consent;
use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->terms = Consent::factory()->for($this->event)->create(['content' => '<p>Regulamin</p>', 'position' => 1]);
    $this->photos = Consent::factory()->for($this->event)->create(['content' => '<p>Zdjęcia</p>', 'position' => 2]);

    $this->url = route('admin.events.consents.move', [$this->event, $this->photos]);
    $this->order = fn (): array => $this->event->consents()->pluck('content')->all();
});

test('only administrators move consents', function () {
    $this->patch($this->url, ['direction' => 'up'])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->patch($this->url, ['direction' => 'up'])->assertForbidden();

    expect(($this->order)())->toBe(['<p>Regulamin</p>', '<p>Zdjęcia</p>']);
});

test('a consent moves one place within its event', function () {
    $other = Consent::factory()->create(['position' => 1]);
    $this->actingAs(User::factory()->admin()->create());

    $this->patch($this->url, ['direction' => 'up'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.events.edit', $this->event));

    expect(($this->order)())->toBe(['<p>Zdjęcia</p>', '<p>Regulamin</p>'])
        ->and($other->fresh()->position)->toBe(1);
});
