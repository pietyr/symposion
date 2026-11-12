<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Actions\Registrations\CancelOwnRegistration;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class CancelRegistrationController extends Controller
{
    /**
     * Cancel the participant's own pending registration.
     */
    public function __invoke(Registration $registration, CancelOwnRegistration $cancelOwnRegistration): RedirectResponse
    {
        Gate::authorize('cancel', $registration);

        $cancelOwnRegistration->handle($registration);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Registration cancelled.')]);

        return to_route('account.registrations.index');
    }
}
