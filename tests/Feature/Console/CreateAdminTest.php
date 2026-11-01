<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('it creates a verified administrator', function () {
    $this->artisan('app:create-admin')
        ->expectsQuestion('Name', 'Anna Nowak')
        ->expectsQuestion('Email', 'anna@example.com')
        ->expectsQuestion('Password', 'Secret-password-123')
        ->assertSuccessful();

    $admin = User::query()->where('email', 'anna@example.com')->sole();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('Secret-password-123', $admin->password))->toBeTrue();
});
