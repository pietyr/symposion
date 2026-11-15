<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('account.profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('account.profile.update'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'profession' => 'laboratory_technician',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('the academic title can be changed and removed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('account.profile.update'), [
        'academic_title' => 'dr n. med.',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => $user->email,
        'profession' => 'laboratory_technician',
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->academic_title)->toBe('dr n. med.');

    $this->actingAs($user)->patch(route('account.profile.update'), [
        'academic_title' => '',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => $user->email,
        'profession' => 'laboratory_technician',
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->academic_title)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('account.profile.update'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $user->email,
            'profession' => 'laboratory_technician',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('account.profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account, which stays with the organiser', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('account.profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh()?->trashed())->toBeTrue();
});

test('deleting the account cancels the unpaid registrations and keeps the paid ones', function () {
    $user = User::factory()->create();
    $pending = Registration::factory()->for($user)->create();
    $paid = Registration::factory()->for($user)->paid()->create();

    $this->actingAs($user)
        ->delete(route('account.profile.destroy'), ['password' => 'password'])
        ->assertSessionHasNoErrors();

    expect($pending->refresh()->status->value)->toBe('cancelled')
        ->and($paid->refresh()->status->value)->toBe('paid');
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('account.profile.edit'))
        ->delete(route('account.profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('account.profile.edit'));

    expect($user->fresh()?->trashed())->toBeFalse();
});
