<?php

declare(strict_types=1);

use App\Http\Controllers\Account\CancelRegistrationController;
use App\Http\Controllers\Account\RegistrationController;
use App\Http\Controllers\Account\Settings\ProfileController;
use App\Http\Controllers\Account\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('ustawienia', fn () => to_route('account.profile.edit'))->name('settings');

    Route::get('ustawienia/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('ustawienia/profil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/', 'account/Dashboard')->name('dashboard');

    Route::get('zapisy', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::patch('zapisy/{registration}/anuluj', CancelRegistrationController::class)->name('registrations.cancel');

    Route::delete('ustawienia/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('ustawienia/bezpieczenstwo', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('ustawienia/haslo', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::inertia('ustawienia/wyglad', 'account/settings/Appearance')->name('appearance.edit');
});
