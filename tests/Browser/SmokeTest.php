<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;

test('public pages render without javascript errors or console output', function () {
    visit([
        route('home', absolute: false),
        route('events.index', absolute: false),
        route('events.past', absolute: false),
        route('login', absolute: false),
        route('register', absolute: false),
    ])->assertNoSmoke();
});

test('a visitor opens an event from the home page', function () {
    $event = Event::factory()->visible()->create();

    visit(route('home', absolute: false))
        ->click('@event-card')
        ->assertPathIs(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false))
        ->assertSee($event->title)
        ->assertNoSmoke();
});

test('an administrator signs in and opens the admin panel from the user menu', function () {
    User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    visit(route('login', absolute: false))
        ->fill('email', 'admin@example.com')
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertPathIs(route('account.dashboard', absolute: false))
        ->assertSee('Panel użytkownika')
        ->click('@sidebar-menu-button')
        ->click('[role="menuitem"]:has-text("Panel admina")')
        ->assertPathIs(route('admin.dashboard', absolute: false))
        ->assertSee('Administracja')
        ->assertSee('Narzędzia')
        ->assertNoJavaScriptErrors();

    $this->assertAuthenticated();
});

test('an administrator switches between panels from the sidebar', function () {
    $this->actingAs(User::factory()->admin()->create());

    visit(route('account.dashboard', absolute: false))
        ->click('Panel admina')
        ->assertPathIs(route('admin.dashboard', absolute: false))
        ->click('Panel użytkownika')
        ->assertPathIs(route('account.dashboard', absolute: false))
        ->assertNoJavaScriptErrors();
});

test('other users do not get a link to the admin panel', function () {
    $this->actingAs(User::factory()->create());

    visit(route('account.dashboard', absolute: false))
        ->assertSee('Panel użytkownika')
        ->assertDontSee('Panel admina');
});

test('event cards load their default banner', function () {
    Event::factory()->visible()->create();

    $page = visit(route('events.index', absolute: false));
    $page->assertVisible('@event-card-banner')->wait(1);

    expect($page->script('document.querySelector("[data-test=event-card-banner]").naturalWidth'))->toBeGreaterThan(0)
        ->and($page->script('document.querySelector("[data-test=event-card-banner]").currentSrc'))->toContain('/images/event-banners/thumbnail-');
});

test('the event page banner spans the window without a horizontal scrollbar', function () {
    $event = Event::factory()->visible()->create();

    $page = visit(route('events.show', ['event' => $event, 'slug' => $event->slug], absolute: false));
    $page->assertVisible('@event-page-banner')->wait(1);

    expect($page->script('document.querySelector("[data-test=event-page-banner]").naturalWidth'))->toBeGreaterThan(0)
        ->and($page->script('document.querySelector("[data-test=event-page-banner]").currentSrc'))->toContain('/images/event-banners/banner-')
        ->and($page->script('document.querySelector("[data-test=event-page-banner]").getBoundingClientRect().left'))->toBeLessThanOrEqual(0)
        ->and($page->script('document.documentElement.scrollWidth <= window.innerWidth'))->toBeTrue();
});
