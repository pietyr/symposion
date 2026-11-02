<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('users without the admin role are forbidden', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('administrators can visit the admin dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/Dashboard'));
});
