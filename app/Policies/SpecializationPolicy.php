<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Specialization;
use App\Models\User;

final class SpecializationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Specialization $specialization): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Specialization $specialization): bool
    {
        return $user->isAdmin();
    }
}
