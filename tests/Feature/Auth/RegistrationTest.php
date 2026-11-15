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
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('account.dashboard', absolute: false));
});

test('new users are registered with the user role', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ]);

    expect(User::query()->sole()->role)->toBe(UserRole::User);
});

test('the role cannot be set during registration', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
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
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ]);

    expect(User::query()->sole()->profession)->toBe('laboratory_technician');
});

test('the first and the last name are required', function (string $field) {
    $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
        $field => '',
    ])->assertSessionHasErrors($field);

    $this->assertGuest();
})->with(['first_name', 'last_name']);

test('the first and the last name are stored apart', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Anna Maria',
        'last_name' => 'Nowak-Kowalska',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ]);

    $user = User::query()->sole();

    expect($user->first_name)->toBe('Anna Maria')
        ->and($user->last_name)->toBe('Nowak-Kowalska')
        ->and($user->name)->toBe('Anna Maria Nowak-Kowalska');
});

test('the academic title is optional', function (?string $academicTitle) {
    $this->post(route('register.store'), [
        'academic_title' => $academicTitle,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ])->assertSessionHasNoErrors();

    expect(User::query()->sole()->academic_title)->toBe($academicTitle);
})->with([
    'given' => ['dr n. med.'],
    'left empty' => [null],
]);

test('the academic title fits in fifty characters', function () {
    $this->post(route('register.store'), [
        'academic_title' => str_repeat('a', 51),
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ])->assertSessionHasErrors('academic_title');

    $this->assertGuest();
});

test('a known profession is required', function (?string $profession) {
    $this->post(route('register.store'), [
        'first_name' => 'Test',
        'last_name' => 'User',
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
