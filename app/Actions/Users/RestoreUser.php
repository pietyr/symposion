<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final class RestoreUser
{
    /**
     * Bring a deleted account back; registrations cancelled by the deletion stay cancelled.
     */
    public function handle(User $user): void
    {
        $user->restore();
    }
}
