<?php

declare(strict_types=1);

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;

test('only administrators confirm payments', function () {
    $registration = Registration::factory()->create();

    $this->patch(route('admin.registrations.confirm', $registration))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())
        ->patch(route('admin.registrations.confirm', $registration))
        ->assertForbidden();

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Pending);
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('a pending registration becomes paid', function () {
        $this->freezeSecond();
        $registration = Registration::factory()->create();

        $this->patch(route('admin.registrations.confirm', $registration))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.registrations.show', $registration));

        expect($registration->fresh())
            ->status->toBe(RegistrationStatus::Paid)
            ->confirmed_at->toEqual(now());
    });

    test('only a pending registration can be confirmed', function (string $state) {
        $registration = Registration::factory()->{$state}()->create(['confirmed_at' => null]);

        $this->patch(route('admin.registrations.confirm', $registration))
            ->assertSessionHasErrors('status');

        expect($registration->fresh()->confirmed_at)->toBeNull();
    })->with(['paid', 'cancelled']);
});
