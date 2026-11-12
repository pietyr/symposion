<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Ordering\MoveListItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveListItemRequest;
use App\Models\Event;
use App\Models\EventSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class MoveEventSectionController extends Controller
{
    /**
     * Move a content section one place up or down.
     */
    public function __invoke(MoveListItemRequest $request, Event $event, EventSection $section, MoveListItem $moveListItem): RedirectResponse
    {
        Gate::authorize('update', $event);

        $moveListItem->handle($section, $section->siblings(), $request->direction());

        return to_route('admin.events.edit', $event);
    }
}
