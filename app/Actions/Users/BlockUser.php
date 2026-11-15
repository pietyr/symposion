<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\RegistrationStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BlockUser
{
    /**
     * Block the account; its pending registrations are cancelled, so they stop taking seats (ADR-025).
     *
     * @throws ValidationException when the account is already blocked
     */
    public function handle(User $user): void
    {
        if ($user->isBlocked()) {
            throw ValidationException::withMessages([
                'account' => __('This account is already blocked.'),
            ]);
        }

        DB::transaction(function () use ($user): void {
            $user->forceFill(['blocked_at' => now()])->save();

            $user->registrations()
                ->where('status', RegistrationStatus::Pending)
                ->update(['status' => RegistrationStatus::Cancelled]);
        });
    }
}
