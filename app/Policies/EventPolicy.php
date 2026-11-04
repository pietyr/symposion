<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class EventPolicy
{
    /**
     * Hidden events must look as if they did not exist, so they answer 404 rather than 403.
     */
    public function view(?User $user, Event $event): Response
    {
        return $event->is_published ? Response::allow() : Response::denyAsNotFound();
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Event $event): bool
    {
        return $user->isAdmin() && $event->trashed();
    }
}
