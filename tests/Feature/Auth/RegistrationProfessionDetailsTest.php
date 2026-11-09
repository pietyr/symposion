<?php

declare(strict_types=1);

use App\Models\User;

/**
 * @param  array<string, string|null>  $details
 */
function registerWithDetails(string $profession, array $details): void
{
    test()->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => $profession,
        ...$details,
    ]);
}

$allDetails = [
    'university' => 'Uniwersytet Medyczny w Łodzi',
    'student_number' => '123456',
    'company' => 'Pharma sp. z o.o.',
    'profession_name' => 'Dietetyk',
];

test('each profession keeps only its own details', function (string $profession, array $expected) use ($allDetails) {
    registerWithDetails($profession, [...$allDetails, 'license_number' => '5425740']);

    $this->assertAuthenticated();
    expect(User::query()->sole()->only(array_keys($allDetails)))->toBe([
        'university' => null,
        'student_number' => null,
        'company' => null,
        'profession_name' => null,
        ...$expected,
    ]);
})->with([
    'student' => ['student', ['university' => 'Uniwersytet Medyczny w Łodzi', 'student_number' => '123456']],
    'medical representative' => ['medical_representative', ['company' => 'Pharma sp. z o.o.']],
    'other' => ['other', ['profession_name' => 'Dietetyk']],
    'doctor' => ['doctor', []],
]);

test('the details of the chosen profession are required', function (string $profession, string $missing) use ($allDetails) {
    registerWithDetails($profession, [...$allDetails, $missing => '']);

    $this->assertGuest();
})->with([
    ['student', 'university'],
    ['student', 'student_number'],
    ['medical_representative', 'company'],
    ['other', 'profession_name'],
]);
