<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\User;

test('only administrators block and unblock users', function () {
    $user = User::factory()->create();
    $blocked = User::factory()->blocked()->create();

    $this->patch(route('admin.users.block', $user))->assertRedirect(route('login'));
    $this->patch(route('admin.users.unblock', $blocked))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->patch(route('admin.users.block', $user))->assertForbidden();
    $this->patch(route('admin.users.unblock', $blocked))->assertForbidden();

    expect($user->refresh()->isBlocked())->toBeFalse()
        ->and($blocked->refresh()->isBlocked())->toBeTrue();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('blocking cancels the pending registrations and keeps the paid ones', function () {
        $user = User::factory()->create();
        $pending = Registration::factory()->for($user)->create();
        $paid = Registration::factory()->for($user)->paid()->create();
        $someoneElses = Registration::factory()->create();

        $this->patch(route('admin.users.block', $user))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.show', $user));

        expect($user->refresh()->isBlocked())->toBeTrue()
            ->and($pending->refresh()->status->value)->toBe('cancelled')
            ->and($paid->refresh()->status->value)->toBe('paid')
            ->and($someoneElses->refresh()->status->value)->toBe('pending');
    });

    test('a blocked user cannot be blocked again', function () {
        $user = User::factory()->create(['blocked_at' => now()->subWeek()]);
        $blockedAt = $user->blocked_at;

        $this->patch(route('admin.users.block', $user))->assertSessionHasErrors('account');

        expect($user->refresh()->blocked_at?->toIso8601String())->toBe($blockedAt?->toIso8601String());
    });

    test('unblocking lets the user in again without bringing the cancelled registrations back', function () {
        $user = User::factory()->blocked()->create();
        $cancelled = Registration::factory()->for($user)->cancelled()->create();

        $this->patch(route('admin.users.unblock', $user))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.show', $user));

        expect($user->refresh()->isBlocked())->toBeFalse()
            ->and($cancelled->refresh()->status->value)->toBe('cancelled');
    });

    test('a user who is not blocked cannot be unblocked', function () {
        $this->patch(route('admin.users.unblock', User::factory()->create()))->assertSessionHasErrors('account');
    });

    test('administrator accounts are left alone', function () {
        $admin = User::factory()->admin()->create();

        $this->patch(route('admin.users.block', $admin))->assertForbidden();

        expect($admin->refresh()->isBlocked())->toBeFalse();
    });
});
