<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateEvent;
use App\Actions\Events\DeleteEvent;
use App\Actions\Events\SaveEventBanners;
use App\Actions\Events\UpdateEvent;
use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Enums\EventFormat;
use App\Enums\EventSort;
use App\Enums\Profession;
use App\Enums\Voivodeship;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\ListEventsRequest;
use App\Http\Requests\Admin\Events\StoreEventRequest;
use App\Http\Requests\Admin\Events\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Specialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class EventController extends Controller
{
    /**
     * Show the nearest and the most recently finished events; choosing an order,
     * filtering or opening the deleted events switches to a full paginated list.
     */
    public function index(ListEventsRequest $request): Response
    {
        Gate::authorize('viewAny', Event::class);

        $isOverview = $request->wantsOverview();

        return Inertia::render('admin/events/Index', [
            'overview' => $isOverview ? [
                'upcoming' => EventResource::collection(Event::query()->withRegistrationsCount()->sortedBy(EventSort::Upcoming)->limit(5)->get()),
                'past' => EventResource::collection(Event::query()->withRegistrationsCount()->sortedBy(EventSort::Past)->limit(5)->get()),
            ] : null,
            'events' => $isOverview ? null : EventResource::collection(
                Event::query()
                    ->when($request->showsDeleted(), fn ($query) => $query->onlyTrashed())
                    ->filteredBy(...$request->eventFilters())
                    ->withRegistrationsCount()
                    ->sortedBy($request->sort())
                    ->paginate(15)
                    ->withQueryString(),
            ),
            'filters' => $request->selectedFilters(),
            'filterOptions' => $request->filterOptions(),
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
    public function store(StoreEventRequest $request, CreateEvent $createEvent, SaveEventBanners $saveEventBanners): RedirectResponse
    {
        Gate::authorize('create', Event::class);

        $event = $createEvent->handle($request->eventAttributes(), $request->eventProfessions(), $request->eventSpecializations());
        $saveEventBanners->handle($event, ...$request->eventBanners());

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
            'event' => EventResource::make($event->load(['professionEntries', 'specializations', 'sections', 'benefits.options'])),
            ...$this->formOptions(),
            'benefitTypes' => BenefitType::options(),
            'benefitAttendances' => Attendance::onlyOptions(),
        ]);
    }

    /**
     * Update the event.
     */
    public function update(UpdateEventRequest $request, Event $event, UpdateEvent $updateEvent, SaveEventBanners $saveEventBanners): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateEvent->handle($event, $request->eventAttributes(), $request->eventProfessions(), $request->eventSpecializations());
        $saveEventBanners->handle($event, ...$request->eventBanners());

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
     * @return array{formats: list<array{value: string, label: string}>, voivodeships: list<array{value: string, label: string}>, professions: list<array{value: string, label: string}>, specializations: list<array{value: string, label: string}>}
     */
    private function formOptions(): array
    {
        return [
            'formats' => EventFormat::options(),
            'voivodeships' => Voivodeship::options(),
            'professions' => Profession::options(),
            'specializations' => Specialization::options(),
        ];
    }
}
