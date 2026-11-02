<?php

declare(strict_types=1);

use App\Models\User;

test('public pages render without javascript errors or console output', function () {
    visit([
        route('home', absolute: false),
        route('login', absolute: false),
        route('register', absolute: false),
    ])->assertNoSmoke();
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
        ->click('@sidebar-menu-button')
        ->click('Panel administratora')
        ->assertPathIs(route('admin.dashboard', absolute: false))
        ->assertNoJavaScriptErrors();

    $this->assertAuthenticated();
});
