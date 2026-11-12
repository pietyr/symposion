<?php

declare(strict_types=1);

use App\Http\Controllers\Site\EventController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PastEventController;
use App\Http\Controllers\Site\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('wydarzenia', [EventController::class, 'index'])->name('events.index');
Route::get('wydarzenia/zakonczone', PastEventController::class)->name('events.past');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('wydarzenie/{event}/{slug}/zapis', [RegistrationController::class, 'create'])
        ->whereNumber('event')
        ->name('events.registration.create');
    Route::post('wydarzenie/{event}/{slug}/zapis', [RegistrationController::class, 'store'])
        ->whereNumber('event')
        ->name('events.registration.store');
});

Route::get('wydarzenie/{event}/{slug?}', [EventController::class, 'show'])
    ->whereNumber('event')
    ->name('events.show');
