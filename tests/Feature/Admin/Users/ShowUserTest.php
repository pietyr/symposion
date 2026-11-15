<?php

declare(strict_types=1);

use App\Models\Registration;
use App\Models\Specialization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('only administrators see a user', function () {
    $user = User::factory()->create();

    $this->get(route('admin.users.show', $user))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.show', $user))
        ->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('the user comes with the professional profile and the account state', function () {
        $doctor = User::factory()->doctor()->unverified()->create(['academic_title' => 'dr n. med.']);
        $doctor->specializations()->attach([
            Specialization::factory()->create(['name' => 'Urologia'])->id,
            Specialization::factory()->create(['name' => 'Kardiologia'])->id,
        ]);

        $this->get(route('admin.users.show', $doctor))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Show')
                ->where('account.id', $doctor->id)
                ->where('account.academic_title', 'dr n. med.')
                ->where('account.name', $doctor->name)
                ->where('account.profession_label', 'Lekarz')
                ->where('account.license_number', $doctor->license_number)
                ->where('account.specializations', ['Kardiologia', 'Urologia'])
                ->where('account.is_admin', false)
                ->where('account.email_verified_at', null)
                ->where('account.created_at', $doctor->created_at?->toIso8601String()),
            );
    });

    test('the registrations of the user come newest first, cancelled and of deleted events too', function () {
        $user = User::factory()->create();
        $older = Registration::factory()->for($user)->cancelled()->create(['created_at' => now()->subDay()]);
        $newer = Registration::factory()->for($user)->paid()->create();
        $newer->event->delete();
        Registration::factory()->create();

        $this->get(route('admin.users.show', $user))
            ->assertInertia(fn (Assert $page) => $page
                ->has('registrations.data', 2)
                ->where('registrations.data.0.id', $newer->id)
                ->where('registrations.data.0.event.is_deleted', true)
                ->where('registrations.data.0.status', 'paid')
                ->where('registrations.data.1.id', $older->id)
                ->where('registrations.data.1.status', 'cancelled'),
            );
    });

    test('the registrations are paginated by fifteen', function () {
        $user = User::factory()->create();
        Registration::factory()->for($user)->count(16)->create();

        $this->get(route('admin.users.show', [$user, 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('registrations.data', 1)
                ->where('registrations.meta.total', 16),
            );
    });

    test('administrators are shown too', function () {
        $admin = User::factory()->admin()->create();

        $this->get(route('admin.users.show', $admin))
            ->assertInertia(fn (Assert $page) => $page->where('account.is_admin', true));
    });
});
