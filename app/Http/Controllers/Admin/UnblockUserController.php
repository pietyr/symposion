<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Users\UnblockUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class UnblockUserController extends Controller
{
    /**
     * Let a blocked user sign in again.
     */
    public function __invoke(User $user, UnblockUser $unblockUser): RedirectResponse
    {
        Gate::authorize('update', $user);

        $unblockUser->handle($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User unblocked.')]);

        return to_route('admin.users.show', $user);
    }
}
