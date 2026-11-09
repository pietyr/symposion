<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Registrations\ListRegistrationsRequest;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\RegistrationResource;
use App\Models\Registration;
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
