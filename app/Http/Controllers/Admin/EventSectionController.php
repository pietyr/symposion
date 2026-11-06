<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEventSection;
use App\Actions\Events\DeleteEventSection;
use App\Actions\Events\UpdateEventSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\SaveEventSectionRequest;
use App\Models\Event;
use App\Models\EventSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class EventSectionController extends Controller
{
    /**
     * Add a content section to the event.
     */
    public function store(SaveEventSectionRequest $request, Event $event, CreateEventSection $createEventSection): RedirectResponse
    {
        Gate::authorize('update', $event);

        $createEventSection->handle($event, $request->string('title')->value(), $request->string('content')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section added.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Update a content section.
     */
    public function update(SaveEventSectionRequest $request, Event $event, EventSection $section, UpdateEventSection $updateEventSection): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateEventSection->handle($section, $request->string('title')->value(), $request->string('content')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section saved.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Delete a content section.
     */
    public function destroy(Event $event, EventSection $section, DeleteEventSection $deleteEventSection): RedirectResponse
    {
        Gate::authorize('update', $event);

        $deleteEventSection->handle($section);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section deleted.')]);

        return to_route('admin.events.edit', $event);
    }
}
