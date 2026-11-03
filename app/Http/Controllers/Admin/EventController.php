<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEvent;
use App\Actions\Events\DeleteEvent;
use App\Actions\Events\UpdateEvent;
use App\Enums\EventFormat;
use App\Enums\Voivodeship;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\StoreEventRequest;
use App\Http\Requests\Admin\Events\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class EventController extends Controller
{
    /**
     * List events, newest start date first; deleted ones are shown on request.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Event::class);

        $search = $request->string('search')->trim()->value();
        $trashed = $request->boolean('trashed');

        $events = Event::query()
            ->when($trashed, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', fn ($query) => $query->titleContains($search))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/events/Index', [
            'events' => EventResource::collection($events),
            'filters' => [
                'search' => $search,
                'trashed' => $trashed,
            ],
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): Response
    {
        Gate::authorize('create', Event::class);

        return Inertia::render('admin/events/Create', $this->formOptions());
    }

    /**
     * Store a newly created event.
     */
    public function store(StoreEventRequest $request, CreateEvent $createEvent): RedirectResponse
    {
        Gate::authorize('create', Event::class);

        $createEvent->handle($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Event created.')]);

        return to_route('admin.events.index');
    }

    /**
     * Show the form for editing the event.
     */
    public function edit(Event $event): Response
    {
        Gate::authorize('update', $event);

        return Inertia::render('admin/events/Edit', [
            'event' => EventResource::make($event),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the event.
     */
    public function update(UpdateEventRequest $request, Event $event, UpdateEvent $updateEvent): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateEvent->handle($event, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Event updated.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Move the event to the trash.
     */
    public function destroy(Event $event, DeleteEvent $deleteEvent): RedirectResponse
    {
        Gate::authorize('delete', $event);

        $deleteEvent->handle($event);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Event deleted.')]);

        return to_route('admin.events.index');
    }

    /**
     * @return array{formats: list<array{value: string, label: string}>, voivodeships: list<array{value: string, label: string}>}
     */
    private function formOptions(): array
    {
        return [
            'formats' => EventFormat::options(),
            'voivodeships' => Voivodeship::options(),
        ];
    }
}
