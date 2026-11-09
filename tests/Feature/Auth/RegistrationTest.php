<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'student',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account.dashboard', absolute: false));
});

test('new users are registered with the user role', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'student',
    ]);

    expect(User::query()->sole()->role)->toBe(UserRole::User);
});

test('the role cannot be set during registration', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'student',
        'role' => UserRole::Admin->value,
    ]);

    expect(User::query()->sole()->role)->toBe(UserRole::User);
});

test('the registration form offers the professions', function () {
    $this->get(route('register'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('professions.0.value', 'doctor')
            ->where('professions.0.label', 'Lekarz'),
        );
});

test('the profession is stored with the new account', function () {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'student',
    ]);

    expect(User::query()->sole()->profession)->toBe('student');
});

test('a known profession is required', function (?string $profession) {
    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => $profession,
    ])->assertSessionHasErrors('profession');

    $this->assertGuest();
})->with([
    'missing' => [null],
    'unknown' => ['astronaut'],
]);
