<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->participant = User::factory()->create();
});

test('guests are sent to the login page', function () {
    $this->get(route('account.registrations.index'))->assertRedirect(route('login'));
});

test('a participant sees their own registrations, newest first', function () {
    $older = Registration::factory()->for($this->participant)->cancelled()->create(['created_at' => now()->subDay()]);
    $newer = Registration::factory()->for($this->participant)->paid()->create();
    Registration::factory()->create();

    $this->actingAs($this->participant)
        ->get(route('account.registrations.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/registrations/Index')
            ->has('registrations.data', 2)
            ->where('registrations.data.0.id', $newer->id)
            ->where('registrations.data.0.event.title', $newer->event->title)
            ->where('registrations.data.0.status', 'paid')
            ->where('registrations.data.1.id', $older->id)
            ->where('registrations.data.1.status', 'cancelled'),
        );
});

test('registrations of deleted events are hidden from the participant', function () {
    $deleted = Event::factory()->create();
    Registration::factory()->for($this->participant)->for($deleted)->create();
    $deleted->delete();
    $kept = Registration::factory()->for($this->participant)->create();

    $this->actingAs($this->participant)
        ->get(route('account.registrations.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('registrations.data', 1)
            ->where('registrations.data.0.id', $kept->id),
        );
});
