<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Ordering\MoveListItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveListItemRequest;
use App\Models\Consent;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class MoveEventConsentController extends Controller
{
    /**
     * Move a consent one place up or down in the registration form.
     */
    public function __invoke(MoveListItemRequest $request, Event $event, Consent $consent, MoveListItem $moveListItem): RedirectResponse
    {
        Gate::authorize('update', $event);

        $moveListItem->handle($consent, $consent->siblings(), $request->direction());

        return to_route('admin.events.edit', $event);
    }
}
