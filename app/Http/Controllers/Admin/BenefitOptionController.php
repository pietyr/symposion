<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Events\CreateBenefitOption;
use App\Actions\Events\DeleteBenefitOption;
use App\Actions\Events\UpdateBenefitOption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Events\SaveBenefitOptionRequest;
use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class BenefitOptionController extends Controller
{
    /**
     * Add an option to a benefit field.
     */
    public function store(SaveBenefitOptionRequest $request, Event $event, Benefit $benefit, CreateBenefitOption $createBenefitOption): RedirectResponse
    {
        Gate::authorize('update', $event);

        $createBenefitOption->handle($benefit, $request->optionAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option added.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Update an option of a benefit field.
     */
    public function update(SaveBenefitOptionRequest $request, Event $event, Benefit $benefit, BenefitOption $option, UpdateBenefitOption $updateBenefitOption): RedirectResponse
    {
        Gate::authorize('update', $event);

        $updateBenefitOption->handle($option, $request->optionAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option saved.')]);

        return to_route('admin.events.edit', $event);
    }

    /**
     * Delete an option of a benefit field.
     */
    public function destroy(Event $event, Benefit $benefit, BenefitOption $option, DeleteBenefitOption $deleteBenefitOption): RedirectResponse
    {
        Gate::authorize('update', $event);

        $deleteBenefitOption->handle($option);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option deleted.')]);

        return to_route('admin.events.edit', $event);
    }
}
