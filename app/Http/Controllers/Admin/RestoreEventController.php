<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RestoreEvent;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class RestoreEventController extends Controller
{
    /**
     * Restore a deleted event.
     */
    public function __invoke(Event $event, RestoreEvent $restoreEvent): RedirectResponse
    {
        Gate::authorize('restore', $event);

        $restoreEvent->handle($event);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Event restored.')]);

        return to_route('admin.events.edit', $event);
    }
}
