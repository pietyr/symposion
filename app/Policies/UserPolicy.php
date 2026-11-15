<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Administrators change their own details in the account settings, not here (ADR-025).
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && ! $model->isAdmin();
    }
}
