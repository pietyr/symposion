<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Registrations\SearchParticipantsRequest;
use App\Http\Resources\ParticipantOptionResource;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class SearchParticipantsController extends Controller
{
    /**
     * Users matching the typed text, for the participant field of a new registration.
     */
    public function __invoke(SearchParticipantsRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('create', Registration::class);

        return ParticipantOptionResource::collection(
            User::query()
                ->matchingSearch($request->search())
                ->orderBy('name')
                ->limit(20)
                ->get(),
        );
    }
}
