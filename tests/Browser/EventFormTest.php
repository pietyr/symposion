<?php

declare(strict_types=1);

use App\Enums\Profession;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        ->click('@event-tab-registration')
        ->assertMissing('#seats_online')
        ->fill('seats_in_person', '80')
        ->click('@event-tab-appearance')
        ->fill('primary_color', '#0f766e')
        ->click('@save-event-button')
        ->assertPathIs(route('admin.events.index', absolute: false))
        ->assertNoJavaScriptErrors();

    $event = Event::with('professionEntries')->sole();

    expect($event->title)->toBe('Forum Testowe')
        ->and($event->professions)->toHaveCount(count(Profession::cases()) - 1)
        ->and($event->professions)->not->toContain(Profession::Student)
        ->and($event->primary_color)->toBe('#0f766e')
        ->and($event->seats_in_person)->toBe(80);
});

test('a validation error opens the tab with the invalid field', function () {
    visit(route('admin.events.create', absolute: false))
        ->click('@event-tab-audience')
        ->click('@save-event-button')
        ->assertSee('Pole tytuł jest wymagane')
        ->assertVisible('#title');
});

test('an invalid color opens the appearance tab', function () {
    $event = Event::factory()->withProfessions()->create();

    visit(route('admin.events.edit', $event, absolute: false))
        ->click('@event-tab-appearance')
        ->fill('accent_color', 'czerwony')
        ->click('[role="tab"]:has-text("Dane")')
        ->click('@save-event-button')
        ->assertSee('Format pola kolor akcentu jest nieprawidłowy')
        ->assertVisible('#accent_color');
});

test('uploading a banner on the edit form keeps the other fields', function () {
    Storage::fake('public');

    $event = Event::factory()->withProfessions()->create(['title' => 'Stary tytuł']);
    $professions = $event->professionEntries()->pluck('profession')->sort()->values()->all();
    $banner = UploadedFile::fake()->image('baner.png', 1200, 400);

    visit(route('admin.events.edit', $event, absolute: false))
        ->fill('title', 'Nowy tytuł')
        ->click('@event-tab-appearance')
        ->attach('#large_banner', $banner->getPathname())
        ->click('@save-event-button')
        ->assertSee('Wydarzenie zostało zaktualizowane')
        ->assertNoJavaScriptErrors();

    $event->refresh();

    expect($event->title)->toBe('Nowy tytuł')
        ->and($event->large_banner_path)->toStartWith("events/{$event->id}/")
        ->and($event->professionEntries()->pluck('profession')->sort()->values()->all())->toBe($professions);

    Storage::disk('public')->assertExists((string) $event->large_banner_path);
});

test('an invalid registration window opens the registration tab', function () {
    $event = Event::factory()->withProfessions()->create();

    visit(route('admin.events.edit', $event, absolute: false))
        ->click('@event-tab-registration')
        ->fill('registration_closes_at', $event->ends_at->addDay()->format('Y-m-d\\TH:i'))
        ->click('[role="tab"]:has-text("Dane")')
        ->click('@save-event-button')
        ->assertVisible('#registration_closes_at')
        ->assertNoJavaScriptErrors();
});
