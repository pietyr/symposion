<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateProfile
{
    /**
     * Update the account and professional details; a new email address has to be verified again.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $specializations
     */
    public function handle(User $user, array $attributes, array $specializations): void
    {
        DB::transaction(function () use ($user, $attributes, $specializations): void {
            $user->fill($attributes);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
            $user->specializations()->sync($specializations);
        });
    }
}
