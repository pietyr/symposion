<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\ListUsersRequest;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\RegistrationResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    /**
     * List user accounts, newest first, with the number of their active registrations.
     */
    public function index(ListUsersRequest $request): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('admin/users/Index', [
            'users' => AdminUserResource::collection(
                User::query()
                    ->filteredBy(...$request->userFilters())
                    ->withRegistrationsCount()
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
     * Show an account with its professional profile and registrations, newest first.
     */
    public function show(User $user): Response
    {
        Gate::authorize('view', $user);

        return Inertia::render('admin/users/Show', [
            'account' => ParticipantResource::make($user->load('specializations')),
            'registrations' => RegistrationResource::collection(
                $user->registrations()
                    ->with('event')
                    ->latest()
                    ->latest('id')
                    ->paginate(15),
            ),
        ]);
    }
}
