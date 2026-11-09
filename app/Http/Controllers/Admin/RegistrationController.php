<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Registrations\CreateRegistration;
use App\Enums\Attendance;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Registrations\ListRegistrationsRequest;
use App\Http\Requests\Admin\Registrations\StoreRegistrationRequest;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\RegistrationResource;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class RegistrationController extends Controller
{
    /**
     * List registrations, newest first.
     */
    public function index(ListRegistrationsRequest $request): Response
    {
        Gate::authorize('viewAny', Registration::class);

        return Inertia::render('admin/registrations/Index', [
            'registrations' => RegistrationResource::collection(
                Registration::query()
                    ->with(['event', 'user'])
                    ->filteredBy(...$request->registrationFilters())
                    ->latest()
                    ->latest('id')
                    ->paginate(15)
                    ->withQueryString(),
            ),
            'filters' => $request->selectedFilters(),
            'filterOptions' => $request->filterOptions(),
        ]);
    }

    /**
     * Show the form for adding a registration by hand.
     */
    public function create(): Response
    {
        Gate::authorize('create', Registration::class);

        return Inertia::render('admin/registrations/Create', [
            'events' => array_values(Event::query()
                ->latest('starts_at')
                ->get()
                ->map(fn (Event $event): array => [
                    'value' => (string) $event->id,
                    'label' => $event->title.' · '.$event->starts_at->format('d.m.Y'),
                    'attendances' => array_map(fn (Attendance $attendance): array => [
                        'value' => $attendance->value,
                        'label' => $attendance->label(),
                        'price' => $event->priceFor($attendance) ?? 0,
                        'seats_left' => $event->seatsLeft($attendance),
                    ], $event->attendances()),
                ])
                ->all()),
        ]);
    }

    /**
     * Store a registration added by hand.
     */
    public function store(StoreRegistrationRequest $request, CreateRegistration $createRegistration): RedirectResponse
    {
        Gate::authorize('create', Registration::class);

        $registration = $createRegistration->handle(
            $request->event(),
            $request->participant(),
            $request->attendance(),
            $request->amountInGrosze(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Registration added.')]);

        return to_route('admin.registrations.show', $registration);
    }

    /**
     * Show a registration with the participant's professional profile.
     */
    public function show(Registration $registration): Response
    {
        Gate::authorize('view', $registration);

        $registration->load(['event', 'user.specializations']);

        return Inertia::render('admin/registrations/Show', [
            'registration' => RegistrationResource::make($registration),
            'participant' => ParticipantResource::make($registration->user),
        ]);
    }
}
