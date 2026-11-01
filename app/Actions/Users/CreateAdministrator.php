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
    public function handle(string $name, string $email, string $password): User
    {
        $user = new User([
            'name' => $name,
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
