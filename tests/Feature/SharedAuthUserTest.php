<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests share no authenticated user', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user', null));
});

test('the authenticated user is shared with only the public fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('auth.user', fn (Assert $sharedUser) => $sharedUser
                ->where('id', $user->id)
                ->where('name', $user->name)
                ->where('email', $user->email)
                ->where('email_verified_at', $user->email_verified_at?->toIso8601String())
                ->where('is_admin', false),
            ),
        );
});

test('administrators are flagged in the shared user', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user.is_admin', true));
});
