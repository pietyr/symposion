<?php

declare(strict_types=1);

use App\Http\Controllers\Site\EventController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'site/Home')->name('home');

Route::get('wydarzenie/{event}/{slug?}', [EventController::class, 'show'])
    ->whereNumber('event')
    ->name('events.show');
