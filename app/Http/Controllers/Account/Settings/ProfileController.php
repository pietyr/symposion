<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account\Settings;

use App\Actions\Users\UpdateProfile;
use App\Enums\Profession;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Settings\ProfileDeleteRequest;
use App\Http\Requests\Account\Settings\ProfileUpdateRequest;
use App\Http\Resources\ProfessionalProfileResource;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request, #[CurrentUser] User $user): Response
    {
        return Inertia::render('account/settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'profile' => ProfessionalProfileResource::make($user->load('specializations')),
            'professions' => Profession::profileOptions(),
            'specializations' => Specialization::options(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, #[CurrentUser] User $user, UpdateProfile $updateProfile): RedirectResponse
    {
        $updateProfile->handle($user, $request->profileAttributes(), $request->specializationIds());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('account.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
