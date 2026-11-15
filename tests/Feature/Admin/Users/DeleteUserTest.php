<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\User;

test('only administrators delete and restore users', function () {
    $user = User::factory()->create();
    $deleted = User::factory()->create();
    $deleted->delete();

    $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('login'));
    $this->patch(route('admin.users.restore', $deleted))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->delete(route('admin.users.destroy', $user))->assertForbidden();
    $this->patch(route('admin.users.restore', $deleted))->assertForbidden();

    expect($user->refresh()->trashed())->toBeFalse()
        ->and($deleted->refresh()->trashed())->toBeTrue();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('deleting an account keeps it in the database and cancels the pending registrations', function () {
        $user = User::factory()->create();
        $pending = Registration::factory()->for($user)->create();
        $paid = Registration::factory()->for($user)->paid()->create();

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        expect($user->refresh()->trashed())->toBeTrue()
            ->and($pending->refresh()->status->value)->toBe('cancelled')
            ->and($paid->refresh()->status->value)->toBe('paid');
    });

    test('a deleted account comes back without its cancelled registrations', function () {
        $user = User::factory()->create();
        $cancelled = Registration::factory()->for($user)->cancelled()->create();
        $user->delete();

        $this->patch(route('admin.users.restore', $user))
            ->assertRedirect(route('admin.users.show', $user));

        expect($user->refresh()->trashed())->toBeFalse()
            ->and($cancelled->refresh()->status->value)->toBe('cancelled');
    });

    test('an account that is not deleted cannot be restored', function () {
        $this->patch(route('admin.users.restore', User::factory()->create()))->assertForbidden();
    });

    test('administrator accounts cannot be deleted', function () {
        $admin = User::factory()->admin()->create();

        $this->delete(route('admin.users.destroy', $admin))->assertForbidden();

        expect($admin->refresh()->trashed())->toBeFalse();
    });
});
