<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateConsent;
use App\Actions\Events\DeleteConsent;
use App\Actions\Events\UpdateConsent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\SaveEventConsentRequest;
use App\Models\Consent;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class EventConsentController extends Controller
{
    /**
     * Add a consent to the event's registration form.
     */
    public function store(SaveEventConsentRequest $request, Event $event, CreateConsent $createConsent): RedirectResponse
    {
        Gate::authorize('update', $event);

        $createConsent->handle($event, $request->string('content')->value(), $request->boolean('is_required'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Consent added.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Update a consent.
     */
    public function update(SaveEventConsentRequest $request, Event $event, Consent $consent, UpdateConsent $updateConsent): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateConsent->handle($consent, $request->string('content')->value(), $request->boolean('is_required'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Consent saved.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Delete a consent.
     */
    public function destroy(Event $event, Consent $consent, DeleteConsent $deleteConsent): RedirectResponse
    {
        Gate::authorize('update', $event);

        $deleteConsent->handle($consent);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Consent deleted.')]);

        return to_route('admin.events.edit', $event);
    }
}
