<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\MoveEventSection;
use App\Enums\MoveDirection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\MoveEventSectionRequest;
use App\Models\Event;
use App\Models\EventSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class MoveEventSectionController extends Controller
{
    /**
     * Move a content section one place up or down.
     */
    public function __invoke(MoveEventSectionRequest $request, Event $event, EventSection $section, MoveEventSection $moveEventSection): RedirectResponse
    {
        Gate::authorize('update', $event);

        $moveEventSection->handle($section, MoveDirection::from($request->string('direction')->value()));

        return to_route('admin.events.edit', $event);
    }
}
