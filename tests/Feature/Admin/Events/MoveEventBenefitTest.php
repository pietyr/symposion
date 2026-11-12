<?php

declare(strict_types=1);

use App\Models\Benefit;
use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->event = Event::factory()->create();
    $this->dinner = Benefit::factory()->for($this->event)->create(['name' => 'Kolacja', 'position' => 1]);
    $this->hotel = Benefit::factory()->for($this->event)->create(['name' => 'Nocleg', 'position' => 2]);

    $this->order = fn (): array => $this->event->benefits()->pluck('name')->all();
});

test('only administrators move benefit fields', function () {
    $url = route('admin.events.benefits.move', [$this->event, $this->hotel]);

    $this->patch($url, ['direction' => 'up'])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->patch($url, ['direction' => 'up'])->assertForbidden();

    expect(($this->order)())->toBe(['Kolacja', 'Nocleg']);
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('a benefit field moves one place within its event', function () {
        $other = Benefit::factory()->create(['position' => 1]);

        $this->patch(route('admin.events.benefits.move', [$this->event, $this->hotel]), ['direction' => 'up'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.edit', $this->event));

        expect(($this->order)())->toBe(['Nocleg', 'Kolacja'])
            ->and($other->fresh()->position)->toBe(1);
    });

    test('a benefit field is only reachable through its own event', function () {
        $this->patch(route('admin.events.benefits.move', [Event::factory()->create(), $this->hotel]), ['direction' => 'up'])
            ->assertNotFound();
    });
});
