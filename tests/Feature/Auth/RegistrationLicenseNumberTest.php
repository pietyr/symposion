<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\LicenseNumber;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, string>  $details
 */
function registerWith(string $profession, ?string $licenseNumber, array $details = []): void
{
    test()->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'profession' => $profession,
        'license_number' => $licenseNumber,
        ...$details,
    ]);
}

test('the form knows which professions ask for a license number', function () {
    $this->get(route('register'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('professions.0.license_number', 'required')
            ->where('professions', fn ($professions) => collect($professions)->pluck('license_number', 'value')->all() === [
                'doctor' => 'required',
                'resident' => 'required',
                'nurse' => 'required',
                'midwife' => 'required',
                'laboratory_diagnostician' => 'required',
                'laboratory_technician' => null,
                'paramedic' => 'optional',
                'student' => null,
                'medical_representative' => null,
                'other' => null,
            ]),
        );
});

test('a valid license number is stored', function (string $profession, ?string $sent, ?string $stored) {
    registerWith($profession, $sent);

    $this->assertAuthenticated();
    expect(User::query()->sole()->license_number)->toBe($stored);
})->with([
    'doctor (example from NIL)' => ['doctor', '5425740', '5425740'],
    'resident' => ['resident', '5425740', '5425740'],
    'nurse' => ['nurse', '1234567P', '1234567P'],
    'nurse, lower case and spaces' => ['nurse', ' 1234567p ', '1234567P'],
    'midwife' => ['midwife', '1234567A', '1234567A'],
    'laboratory diagnostician' => ['laboratory_diagnostician', '12345', '12345'],
    'paramedic with a number' => ['paramedic', '12345', '12345'],
    'paramedic without a number' => ['paramedic', null, null],
]);

test('an invalid or missing license number is rejected', function (string $profession, ?string $licenseNumber) {
    registerWith($profession, $licenseNumber);

    $this->assertGuest();
})->with([
    'doctor, wrong check digit' => ['doctor', '5425741'],
    'doctor, starting with 0' => ['doctor', '0425740'],
    'doctor, remainder of 10' => ['doctor', '9000020'],
    'doctor, six digits' => ['doctor', '542574'],
    'doctor, missing' => ['doctor', null],
    'nurse, letter of a midwife' => ['nurse', '1234567A'],
    'nurse, no letter' => ['nurse', '1234567'],
    'midwife, letter of a nurse' => ['midwife', '1234567P'],
    'laboratory diagnostician, letters' => ['laboratory_diagnostician', '12a45'],
    'laboratory diagnostician, missing' => ['laboratory_diagnostician', null],
]);

test('professions without a license number do not store one', function (string $profession) {
    registerWith($profession, '5425740', [
        'university' => 'Uniwersytet Medyczny',
        'student_number' => '123456',
        'company' => 'Pharma',
        'profession_name' => 'Dietetyk',
    ]);

    $this->assertAuthenticated();
    expect(User::query()->sole()->license_number)->toBeNull();
})->with(['laboratory_technician', 'student', 'medical_representative', 'other']);

test('the doctor check digit follows the rule of the medical chamber', function (string $number, bool $isValid) {
    expect(LicenseNumber::isValidDoctorNumber($number))->toBe($isValid);
})->with([
    ['5425740', true],
    ['1000000', false],
    ['1100000', true],
    ['3010000', false],
]);
