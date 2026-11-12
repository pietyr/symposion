<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BenefitOptionController;
use App\Http\Controllers\Admin\CancelRegistrationController;
use App\Http\Controllers\Admin\ConfirmRegistrationController;
use App\Http\Controllers\Admin\EventBenefitController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventSectionController;
use App\Http\Controllers\Admin\MoveBenefitOptionController;
use App\Http\Controllers\Admin\MoveEventBenefitController;
use App\Http\Controllers\Admin\MoveEventSectionController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\RestoreEventController;
use App\Http\Controllers\Admin\SearchParticipantsController;
use App\Http\Controllers\Admin\SpecializationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'admin/Dashboard')->name('dashboard');

Route::resource('events', EventController::class)->except('show');
Route::patch('events/{event}/restore', RestoreEventController::class)
    ->withTrashed()
    ->name('events.restore');
Route::resource('events.sections', EventSectionController::class)
    ->only(['store', 'update', 'destroy'])
    ->scoped();
Route::patch('events/{event}/sections/{section}/move', MoveEventSectionController::class)
    ->scopeBindings()
    ->name('events.sections.move');
Route::resource('events.benefits', EventBenefitController::class)
    ->only(['store', 'update', 'destroy'])
    ->scoped();
Route::patch('events/{event}/benefits/{benefit}/move', MoveEventBenefitController::class)
    ->scopeBindings()
    ->name('events.benefits.move');
Route::resource('events.benefits.options', BenefitOptionController::class)
    ->only(['store', 'update', 'destroy'])
    ->scoped();
Route::patch('events/{event}/benefits/{benefit}/options/{option}/move', MoveBenefitOptionController::class)
    ->scopeBindings()
    ->name('events.benefits.options.move');

Route::get('registrations/participants', SearchParticipantsController::class)
    ->name('registrations.participants');
Route::patch('registrations/{registration}/confirm', ConfirmRegistrationController::class)
    ->name('registrations.confirm');
Route::patch('registrations/{registration}/cancel', CancelRegistrationController::class)
    ->name('registrations.cancel');
Route::resource('registrations', RegistrationController::class)->only(['index', 'create', 'store', 'show']);
Route::resource('specializations', SpecializationController::class)->except('show');
