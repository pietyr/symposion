<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('deleting an event moves it to the trash', function () {
    $event = Event::factory()->published()->create();

    $this->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.index'));

    expect($event->fresh()->trashed())->toBeTrue();
});

test('a deleted event can be restored', function () {
    $event = Event::factory()->create();
    $event->delete();

    $this->patch(route('admin.events.restore', $event))
        ->assertRedirect(route('admin.events.edit', $event));

    expect($event->fresh()->trashed())->toBeFalse();
});

test('an event that is not deleted cannot be restored', function () {
    $event = Event::factory()->create();

    $this->patch(route('admin.events.restore', $event))->assertForbidden();
});
