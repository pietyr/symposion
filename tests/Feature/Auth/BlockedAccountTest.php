<?php

declare(strict_types=1);

use App\Models\User;

test('a blocked user is signed out on the next request', function () {
    $this->actingAs(User::factory()->blocked()->create())
        ->get(route('account.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'To konto zostało zablokowane. Skontaktuj się z organizatorem.']);

    $this->assertGuest();
});

test('signing in to a blocked account ends on the login page', function () {
    $user = User::factory()->blocked()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

    $this->get(route('account.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users who are not blocked are not affected', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('account.dashboard'))
        ->assertOk();

    $this->assertAuthenticated();
});
