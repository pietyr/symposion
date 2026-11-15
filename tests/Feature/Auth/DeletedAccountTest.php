<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\QueryException;

test('a deleted account cannot sign in', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('the email address of a deleted account stays taken', function () {
    User::factory()->create(['email' => 'anna@example.com'])->delete();

    $this->post(route('register.store'), [
        'first_name' => 'Anna',
        'last_name' => 'Nowak',
        'email' => 'anna@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => 'laboratory_technician',
    ])->assertSessionHasErrors('email');
});

test('an account with registrations cannot be removed from the database for good', function () {
    $user = User::factory()->create();
    Registration::factory()->for($user)->create();

    expect(fn () => $user->forceDelete())->toThrow(QueryException::class);
});
