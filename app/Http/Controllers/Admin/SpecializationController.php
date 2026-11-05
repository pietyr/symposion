<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Specializations\CreateSpecialization;
use App\Actions\Specializations\DeleteSpecialization;
use App\Actions\Specializations\UpdateSpecialization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Specializations\SaveSpecializationRequest;
use App\Http\Resources\SpecializationResource;
use App\Models\Specialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class SpecializationController extends Controller
{
    /**
     * List specializations alphabetically.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Specialization::class);

        return Inertia::render('admin/specializations/Index', [
            'specializations' => SpecializationResource::collection(
                Specialization::query()->orderBy('name')->paginate(15),
            ),
        ]);
    }

    /**
     * Show the form for creating a new specialization.
     */
    public function create(): Response
    {
        Gate::authorize('create', Specialization::class);

        return Inertia::render('admin/specializations/Create');
    }

    /**
     * Store a newly created specialization.
     */
    public function store(SaveSpecializationRequest $request, CreateSpecialization $createSpecialization): RedirectResponse
    {
        Gate::authorize('create', Specialization::class);

        $createSpecialization->handle($request->string('name')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Specialization created.')]);

        return to_route('admin.specializations.index');
    }

    /**
     * Show the form for editing the specialization.
     */
    public function edit(Specialization $specialization): Response
    {
        Gate::authorize('update', $specialization);

        return Inertia::render('admin/specializations/Edit', [
            'specialization' => SpecializationResource::make($specialization),
        ]);
    }

    /**
     * Update the specialization.
     */
    public function update(SaveSpecializationRequest $request, Specialization $specialization, UpdateSpecialization $updateSpecialization): RedirectResponse
    {
        Gate::authorize('update', $specialization);

        $updateSpecialization->handle($specialization, $request->string('name')->value());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Specialization updated.')]);

        return to_route('admin.specializations.index');
    }

    /**
     * Delete the specialization.
     */
    public function destroy(Specialization $specialization, DeleteSpecialization $deleteSpecialization): RedirectResponse
    {
        Gate::authorize('delete', $specialization);

        $deleteSpecialization->handle($specialization);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Specialization deleted.')]);

        return to_route('admin.specializations.index');
    }
}
