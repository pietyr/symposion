<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Users\RestoreUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class RestoreUserController extends Controller
{
    /**
     * Restore a deleted account.
     */
    public function __invoke(User $user, RestoreUser $restoreUser): RedirectResponse
    {
        Gate::authorize('restore', $user);

        $restoreUser->handle($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User restored.')]);

        return to_route('admin.users.show', $user);
    }
}
