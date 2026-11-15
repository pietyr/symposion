<?php

declare(strict_types=1);

use App\Models\Specialization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array<string, mixed>
 */
function userFormData(array $overrides = []): array
{
    return [
        'academic_title' => 'dr n. med.',
        'first_name' => 'Anna',
        'last_name' => 'Nowak',
        'email' => 'anna.nowak@example.com',
        'profession' => 'laboratory_technician',
        ...$overrides,
    ];
}

test('only administrators edit users', function () {
    $user = User::factory()->create();

    $this->get(route('admin.users.edit', $user))->assertRedirect(route('login'));
    $this->put(route('admin.users.update', $user), userFormData())->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.users.edit', $user))->assertForbidden();
    $this->put(route('admin.users.update', $user), userFormData())->assertForbidden();
});

describe('as an administrator', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    test('the form comes with the account and the professional profile', function () {
        $doctor = User::factory()->doctor()->create(['academic_title' => 'lek.']);
        $specialization = Specialization::factory()->create();
        $doctor->specializations()->attach($specialization);

        $this->get(route('admin.users.edit', $doctor))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Edit')
                ->where('account.id', $doctor->id)
                ->where('account.first_name', $doctor->first_name)
                ->where('account.last_name', $doctor->last_name)
                ->where('account.email', $doctor->email)
                ->where('profile.academic_title', 'lek.')
                ->where('profile.profession', 'doctor')
                ->where('profile.license_number', $doctor->license_number)
                ->where('profile.specializations', [(string) $specialization->id])
                ->has('professions', 10)
                ->has('specializations', 1),
            );
    });

    test('the account and the professional profile are updated', function () {
        $user = User::factory()->create(['profession' => 'student', 'university' => 'UMED Łódź', 'student_number' => '123456']);
        $specialization = Specialization::factory()->create();

        $this->put(route('admin.users.update', $user), userFormData([
            'profession' => 'doctor',
            'license_number' => '5425740',
            'specializations' => [$specialization->id],
        ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.show', $user));

        $user->refresh();

        expect($user->academic_title)->toBe('dr n. med.')
            ->and($user->name)->toBe('Anna Nowak')
            ->and($user->email)->toBe('anna.nowak@example.com')
            ->and($user->profession)->toBe('doctor')
            ->and($user->license_number)->toBe('5425740')
            ->and($user->university)->toBeNull()
            ->and($user->student_number)->toBeNull()
            ->and($user->specializations->modelKeys())->toBe([$specialization->id]);
    });

    test('a new email address has to be verified again', function () {
        $user = User::factory()->create();

        $this->put(route('admin.users.update', $user), userFormData())->assertSessionHasNoErrors();

        expect($user->refresh()->email_verified_at)->toBeNull();
    });

    test('the same email address stays verified', function () {
        $user = User::factory()->create();

        $this->put(route('admin.users.update', $user), userFormData(['email' => $user->email]))->assertSessionHasNoErrors();

        expect($user->refresh()->email_verified_at)->not->toBeNull();
    });

    test('the data is checked like in the profile settings', function (Closure $data, string $field) {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->put(route('admin.users.update', $user), userFormData($data()))->assertSessionHasErrors($field);
    })->with([
        'no first name' => [fn () => ['first_name' => ''], 'first_name'],
        'no last name' => [fn () => ['last_name' => ''], 'last_name'],
        'invalid email address' => [fn () => ['email' => 'not-an-email'], 'email'],
        'email address of another user' => [fn () => ['email' => 'taken@example.com'], 'email'],
        'no profession' => [fn () => ['profession' => ''], 'profession'],
        'wrong license number' => [fn () => ['profession' => 'doctor', 'license_number' => '5425741'], 'license_number'],
        'too long academic title' => [fn () => ['academic_title' => str_repeat('a', 51)], 'academic_title'],
    ]);

    test('administrator accounts cannot be edited here', function () {
        $admin = User::factory()->admin()->create();

        $this->get(route('admin.users.edit', $admin))->assertForbidden();
        $this->put(route('admin.users.update', $admin), userFormData())->assertForbidden();
    });
});
