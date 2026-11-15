<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;

test('only administrators verify accounts', function () {
    $user = User::factory()->unverified()->create();

    $this->patch(route('admin.users.verify', $user))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.users.verify', $user))
        ->assertForbidden();

    expect($user->refresh()->email_verified_at)->toBeNull();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('an unverified account is verified', function () {
        Event::fake([Verified::class]);
        $user = User::factory()->unverified()->create();

        $this->patch(route('admin.users.verify', $user))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.show', $user));

        expect($user->refresh()->email_verified_at)->not->toBeNull();
        Event::assertDispatched(Verified::class, fn (Verified $event) => $event->user->is($user));
    });

    test('a verified account keeps its verification date', function () {
        $user = User::factory()->create(['email_verified_at' => now()->subMonth()]);
        $verifiedAt = $user->email_verified_at;

        $this->patch(route('admin.users.verify', $user))->assertSessionHasErrors('account');

        expect($user->refresh()->email_verified_at?->toIso8601String())->toBe($verifiedAt?->toIso8601String());
    });

    test('administrator accounts are left alone', function () {
        $admin = User::factory()->admin()->unverified()->create();

        $this->patch(route('admin.users.verify', $admin))->assertForbidden();

        expect($admin->refresh()->email_verified_at)->toBeNull();
    });
});
