<?php

declare(strict_types=1);

use App\Models\Specialization;
use App\Models\User;
use App\Rules\LicenseNumber;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $data
 */
function updateProfile(User $user, array $data): void
{
    test()->actingAs($user)->patch(route('account.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        ...$data,
    ]);
}

test('the profile page shows the professional details', function () {
    $specialization = Specialization::factory()->create();
    $user = User::factory()->doctor()->create();
    $user->specializations()->attach($specialization);

    $this->actingAs($user)
        ->get(route('account.profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/settings/Profile')
            ->where('profile.profession', 'doctor')
            ->where('profile.license_number', $user->license_number)
            ->where('profile.specializations', [(string) $specialization->id])
            ->has('professions', 10)
            ->has('specializations', 1),
        );
});

test('the professional details are not shared with every page', function () {
    $this->actingAs(User::factory()->doctor()->create())
        ->get(route('account.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->missing('auth.user.license_number'));
});

test('a doctor updates the license number and specializations', function () {
    [$kept, $added] = Specialization::factory()->count(2)->create();
    $user = User::factory()->doctor()->create();
    $user->specializations()->attach($kept);

    updateProfile($user, ['profession' => 'doctor', 'license_number' => '5425740', 'specializations' => [$kept->id, $added->id]]);

    $user->refresh();
    expect($user->license_number)->toBe('5425740')
        ->and($user->specializations->pluck('id')->sort()->values()->all())->toBe([$kept->id, $added->id]);
});

test('changing the profession clears what the old one used', function () {
    $user = User::factory()->doctor()->create();
    $user->specializations()->attach(Specialization::factory()->create());

    updateProfile($user, ['profession' => 'student', 'university' => 'UMED', 'student_number' => '123', 'license_number' => '5425740']);

    $user->refresh();
    expect($user->only('profession', 'license_number', 'university', 'student_number'))
        ->toBe(['profession' => 'student', 'license_number' => null, 'university' => 'UMED', 'student_number' => '123'])
        ->and($user->specializations)->toBeEmpty();
});

test('the same rules apply as at registration', function () {
    $user = User::factory()->doctor()->create();
    $licenseNumber = $user->license_number;

    $this->actingAs($user)
        ->patch(route('account.profile.update'), ['name' => $user->name, 'email' => $user->email, 'profession' => 'doctor', 'license_number' => '5425741'])
        ->assertSessionHasErrors('license_number');

    expect($user->fresh()->license_number)->toBe($licenseNumber);
});

test('users must have a profession, administrators may have none', function () {
    $this->actingAs(User::factory()->create())
        ->patch(route('account.profile.update'), ['name' => 'Jan', 'email' => 'jan@example.com'])
        ->assertSessionHasErrors('profession');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('account.profile.update'), ['name' => 'Admin', 'email' => $admin->email])
        ->assertSessionHasNoErrors();

    expect($admin->fresh()->profession)->toBeNull();
});

test('the doctor factory makes license numbers that pass the check digit', function () {
    User::factory()->doctor()->count(20)->create()->each(
        fn (User $user) => expect(LicenseNumber::isValidDoctorNumber((string) $user->license_number))->toBeTrue(),
    );
});
