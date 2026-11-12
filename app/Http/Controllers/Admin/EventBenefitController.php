<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateBenefit;
use App\Actions\Events\DeleteBenefit;
use App\Actions\Events\UpdateBenefit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\SaveEventBenefitRequest;
use App\Models\Benefit;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class EventBenefitController extends Controller
{
    /**
     * Add a benefit field to the event's registration form.
     */
    public function store(SaveEventBenefitRequest $request, Event $event, CreateBenefit $createBenefit): RedirectResponse
    {
        Gate::authorize('update', $event);

        $createBenefit->handle($event, $request->benefitAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Benefit added.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Update a benefit field.
     */
    public function update(SaveEventBenefitRequest $request, Event $event, Benefit $benefit, UpdateBenefit $updateBenefit): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateBenefit->handle($benefit, $request->benefitAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Benefit saved.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Delete a benefit field.
     */
    public function destroy(Event $event, Benefit $benefit, DeleteBenefit $deleteBenefit): RedirectResponse
    {
        Gate::authorize('update', $event);

        $deleteBenefit->handle($benefit);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Benefit deleted.')]);

        return to_route('admin.events.edit', $event);
    }
}
