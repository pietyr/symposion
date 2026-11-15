<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Validation\ValidationException;

final class UnblockUser
{
    /**
     * Let the user sign in again; registrations cancelled by the block stay cancelled.
     *
     * @throws ValidationException when the account is not blocked
     */
    public function handle(User $user): void
    {
        if (! $user->isBlocked()) {
            throw ValidationException::withMessages([
                'account' => __('This account is not blocked.'),
            ]);
        }

        $user->forceFill(['blocked_at' => null])->save();
    }
}
