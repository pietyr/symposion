<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Users\BlockUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class BlockUserController extends Controller
{
    /**
     * Block the account of a user.
     */
    public function __invoke(User $user, BlockUser $blockUser): RedirectResponse
    {
        Gate::authorize('update', $user);

        $blockUser->handle($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User blocked.')]);

        return to_route('admin.users.show', $user);
    }
}
