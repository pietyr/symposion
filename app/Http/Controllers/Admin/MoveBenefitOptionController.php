<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Ordering\MoveListItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveListItemRequest;
use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class MoveBenefitOptionController extends Controller
{
    /**
     * Move an option one place up or down within its benefit field.
     */
    public function __invoke(MoveListItemRequest $request, Event $event, Benefit $benefit, BenefitOption $option, MoveListItem $moveListItem): RedirectResponse
    {
        Gate::authorize('update', $event);

        $moveListItem->handle($option, $option->siblings(), $request->direction());

        return to_route('admin.events.edit', $event);
    }
}
