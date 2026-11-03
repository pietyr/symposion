<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

final class EventPolicy
{
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
