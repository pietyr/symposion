<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserRole;
use App\Models\User;

final class CreateAdministrator
{
    /**
     * Create an administrator account with a verified email address.
     */
    public function handle(string $firstName, string $lastName, string $email, string $password): User
    {
        $user = new User([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
        ]);

        $user->forceFill([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }
}
