<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\ListEventParticipantsRequest;
use App\Http\Resources\RegistrationResource;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class EventParticipantController extends Controller
{
    /**
     * List the participants of an event by surname; cancelled registrations stay out
     * unless the status filter asks for them (ADR-023).
     */
    public function index(ListEventParticipantsRequest $request, Event $event): Response
    {
        Gate::authorize('viewAny', Registration::class);

        $filters = $request->participantFilters();

        return Inertia::render('admin/events/Participants', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'is_deleted' => $event->trashed(),
            ],
            'registrations' => RegistrationResource::collection(
                $event->registrations()
                    ->with(['user', 'benefits'])
                    ->when($filters['status'] === null, fn ($query) => $query->takingSeats())
                    ->filteredBy(...$filters)
                    ->orderedByParticipantSurname()
                    ->paginate(50)
                    ->withQueryString(),
            ),
            'filters' => $request->selectedFilters(),
            'filterOptions' => $request->filterOptions($event),
        ]);
    }
}
