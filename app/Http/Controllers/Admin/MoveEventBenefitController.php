<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Ordering\MoveListItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveListItemRequest;
use App\Models\Benefit;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class MoveEventBenefitController extends Controller
{
    /**
     * Move a benefit field one place up or down in the registration form.
     */
    public function __invoke(MoveListItemRequest $request, Event $event, Benefit $benefit, MoveListItem $moveListItem): RedirectResponse
    {
        Gate::authorize('update', $event);

        $moveListItem->handle($benefit, $benefit->siblings(), $request->direction());

        return to_route('admin.events.edit', $event);
    }
}
