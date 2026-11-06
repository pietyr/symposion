<?php

declare(strict_types=1);

use App\Enums\Profession;
use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the event form submits the fields of every tab', function () {
    visit(route('admin.events.create', absolute: false))
        ->fill('title', 'Forum Testowe')
        ->click('#voivodeship')
        ->click('[role="option"]:has-text("małopolskie")')
        ->fill('city', 'Kraków')
        ->fill('venue', 'ICE')
        ->fill('starts_at', '2027-05-10T09:00')
        ->fill('ends_at', '2027-05-11T17:00')
        ->fill('price_in_person', '100')
        ->click('@event-tab-audience')
        ->click('#profession_student')
        ->click('@save-event-button')
        ->assertPathIs(route('admin.events.index', absolute: false))
        ->assertNoJavaScriptErrors();

    $event = Event::with('professionEntries')->sole();

    expect($event->title)->toBe('Forum Testowe')
        ->and($event->professions)->toHaveCount(count(Profession::cases()) - 1)
        ->and($event->professions)->not->toContain(Profession::Student);
});

test('a validation error opens the tab with the invalid field', function () {
    visit(route('admin.events.create', absolute: false))
        ->click('@event-tab-audience')
        ->click('@save-event-button')
        ->assertSee('Pole tytuł jest wymagane')
        ->assertVisible('#title');
});
