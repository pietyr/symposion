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
