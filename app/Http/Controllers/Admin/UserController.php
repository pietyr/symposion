<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Users\UpdateProfile;
use App\Enums\Profession;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\ListUsersRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\ProfessionalProfileResource;
use App\Http\Resources\RegistrationResource;
use App\Http\Resources\UserResource;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Show the form for editing the account and the professional profile.
     */
    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('admin/users/Edit', [
            'account' => UserResource::make($user),
            'profile' => ProfessionalProfileResource::make($user->load('specializations')),
            'professions' => Profession::profileOptions(),
            'specializations' => Specialization::options(),
        ]);
    }

    /**
     * Update the account and the professional profile; a new email address has to be verified again.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateProfile $updateProfile): RedirectResponse
    {
        Gate::authorize('update', $user);

        $updateProfile->handle($user, $request->profileAttributes(), $request->specializationIds());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return to_route('admin.users.show', $user);
    }
}
