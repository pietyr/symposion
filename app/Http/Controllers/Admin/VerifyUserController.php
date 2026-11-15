<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Users\VerifyAccount;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class VerifyUserController extends Controller
{
    /**
     * Verify the account of a user by hand.
     */
    public function __invoke(User $user, VerifyAccount $verifyAccount): RedirectResponse
    {
        Gate::authorize('update', $user);

        $verifyAccount->handle($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account verified.')]);

        return to_route('admin.users.show', $user);
    }
}
