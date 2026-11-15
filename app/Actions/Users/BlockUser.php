<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Registrations\CancelPendingRegistrations;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class BlockUser
{
    public function __construct(private CancelPendingRegistrations $cancelPendingRegistrations) {}

    /**
     * Block the account; its pending registrations are cancelled.
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

            $this->cancelPendingRegistrations->handle($user);
        });
    }
}
