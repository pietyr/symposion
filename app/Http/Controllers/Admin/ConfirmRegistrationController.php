<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Registrations\ConfirmRegistration;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class ConfirmRegistrationController extends Controller
{
    /**
     * Confirm the payment of a registration by hand.
     */
    public function __invoke(Registration $registration, ConfirmRegistration $confirmRegistration): RedirectResponse
    {
        Gate::authorize('update', $registration);

        $confirmRegistration->handle($registration);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment confirmed.')]);

        return to_route('admin.registrations.show', $registration);
    }
}
