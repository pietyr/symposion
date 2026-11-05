<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEvent;
use App\Actions\Events\DeleteEvent;
use App\Actions\Events\UpdateEvent;
use App\Enums\EventFormat;
use App\Enums\EventSort;
use App\Enums\Profession;
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
     * Show the nearest and the most recently finished events; choosing an order,
     * searching or opening the deleted events switches to a full paginated list.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Event::class);

        $search = $request->string('search')->trim()->value();
        $trashed = $request->boolean('trashed');
        $sort = $request->enum('sort', EventSort::class) ?? EventSort::UpcomingThenPast;

        $isOverview = $sort === EventSort::UpcomingThenPast && $search === '' && ! $trashed;

        return Inertia::render('admin/events/Index', [
            'overview' => $isOverview ? [
                'upcoming' => EventResource::collection(Event::query()->sortedBy(EventSort::Upcoming)->limit(5)->get()),
                'past' => EventResource::collection(Event::query()->sortedBy(EventSort::Past)->limit(5)->get()),
            ] : null,
            'events' => $isOverview ? null : EventResource::collection(
                Event::query()
                    ->when($trashed, fn ($query) => $query->onlyTrashed())
                    ->when($search !== '', fn ($query) => $query->matchingSearch($search))
                    ->sortedBy($sort)
                    ->paginate(15)
                    ->withQueryString(),
            ),
            'filters' => [
                'search' => $search,
                'trashed' => $trashed,
                'sort' => $sort->value,
            ],
            'sorts' => EventSort::options(),
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

        $createEvent->handle($request->eventAttributes(), $request->eventProfessions());

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
            'event' => EventResource::make($event->load('professionEntries')),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the event.
     */
    public function update(UpdateEventRequest $request, Event $event, UpdateEvent $updateEvent): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateEvent->handle($event, $request->eventAttributes(), $request->eventProfessions());

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
     * @return array{formats: list<array{value: string, label: string}>, voivodeships: list<array{value: string, label: string}>, professions: list<array{value: string, label: string}>}
     */
    private function formOptions(): array
    {
        return [
            'formats' => EventFormat::options(),
            'voivodeships' => Voivodeship::options(),
            'professions' => Profession::options(),
        ];
    }
}
