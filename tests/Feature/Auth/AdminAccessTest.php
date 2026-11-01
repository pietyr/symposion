<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'admin'])
        ->get('/admin-access-test', fn () => 'ok');
});

test('guests are redirected to the login page', function () {
    $this->get('/admin-access-test')
        ->assertRedirect(route('login'));
});

test('users without the admin role are forbidden', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin-access-test')
        ->assertForbidden();
});

test('administrators are allowed', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin-access-test')
        ->assertOk();
});
