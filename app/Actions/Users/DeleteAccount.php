<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Registrations\CancelPendingRegistrations;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class DeleteAccount
{
    public function __construct(private CancelPendingRegistrations $cancelPendingRegistrations) {}

    /**
     * Soft delete the account (ADR-025): its registrations stay with the administrator, the pending ones cancelled.
     */
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $this->cancelPendingRegistrations->handle($user);

            $user->delete();
        });
    }
}
