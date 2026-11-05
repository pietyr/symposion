<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\RestoreEventController;
use App\Http\Controllers\Admin\SpecializationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'admin/Dashboard')->name('dashboard');

Route::resource('events', EventController::class)->except('show');
Route::patch('events/{event}/restore', RestoreEventController::class)
    ->withTrashed()
    ->name('events.restore');

Route::resource('specializations', SpecializationController::class)->except('show');
