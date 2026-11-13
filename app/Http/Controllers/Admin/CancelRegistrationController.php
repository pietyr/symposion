<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Registrations\CancelRegistration;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class CancelRegistrationController extends Controller
{
    /**
     * Cancel a registration and go back where the administrator came from:
     * the registration's page or the participant list of its event (ADR-023).
     */
    public function __invoke(Registration $registration, CancelRegistration $cancelRegistration): RedirectResponse
    {
        Gate::authorize('update', $registration);

        $cancelRegistration->handle($registration);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Registration cancelled.')]);

        return back();
    }
}
