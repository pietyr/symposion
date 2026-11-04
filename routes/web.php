<?php

declare(strict_types=1);

use App\Http\Controllers\Site\EventController;
use App\Http\Controllers\Site\PastEventController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'site/Home')->name('home');

Route::get('wydarzenia', [EventController::class, 'index'])->name('events.index');
Route::get('wydarzenia/zakonczone', PastEventController::class)->name('events.past');
Route::get('wydarzenie/{event}/{slug?}', [EventController::class, 'show'])
    ->whereNumber('event')
    ->name('events.show');
