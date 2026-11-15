<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Validation\ValidationException;

final class VerifyAccount
{
    /**
     * Mark the email address as verified by hand, e.g. when the verification email did not arrive.
     *
     * @throws ValidationException when the account is already verified
     */
    public function handle(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'account' => __('This account is already verified.'),
            ]);
        }

        $user->markEmailAsVerified();

        event(new Verified($user));
    }
}
