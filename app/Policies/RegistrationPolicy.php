<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

final class RegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Registration $registration): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
