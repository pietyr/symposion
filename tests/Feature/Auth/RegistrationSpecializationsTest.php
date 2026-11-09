<?php

declare(strict_types=1);

use App\Models\Specialization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<int, mixed>  $specializations
 */
function registerWithSpecializations(string $profession, array $specializations): void
{
    test()->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => $profession,
        'license_number' => '5425740',
        'specializations' => $specializations,
    ]);
}

test('the form offers the specializations alphabetically', function () {
    Specialization::factory()->create(['name' => 'Urologia']);
    $first = Specialization::factory()->create(['name' => 'Alergologia']);

    $this->get(route('register'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('specializations', 2)
            ->where('specializations.0', ['value' => (string) $first->id, 'label' => 'Alergologia'])
            ->where('professions.0.specializations', true)
            ->where('professions.1.specializations', false),
        );
});

test('a doctor registers with several specializations or none', function (int $count) {
    $specializations = Specialization::factory()->count($count)->create();

    registerWithSpecializations('doctor', $specializations->pluck('id')->all());

    $this->assertAuthenticated();
    expect(User::query()->sole()->specializations->pluck('id')->sort()->values()->all())
        ->toBe($specializations->pluck('id')->sort()->values()->all());
})->with([
    'several' => [3],
    'none' => [0],
]);

test('other professions do not keep specializations', function () {
    $specialization = Specialization::factory()->create();

    registerWithSpecializations('resident', [$specialization->id]);

    $this->assertAuthenticated();
    expect(User::query()->sole()->specializations)->toBeEmpty();
});

test('unknown or repeated specializations are rejected', function (Closure $specializations) {
    registerWithSpecializations('doctor', $specializations());

    $this->assertGuest();
})->with([
    'unknown' => [fn () => [999]],
    'repeated' => [fn () => array_fill(0, 2, Specialization::factory()->create()->id)],
]);
