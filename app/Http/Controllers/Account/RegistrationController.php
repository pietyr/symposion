<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\MyRegistrationResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

final class RegistrationController extends Controller
{
    /**
     * List the participant's registrations, newest first. Registrations of deleted
     * events stay with the administrator only (D3).
     */
    public function index(#[CurrentUser] User $user): Response
    {
        return Inertia::render('account/registrations/Index', [
            'registrations' => MyRegistrationResource::collection(
                $user->registrations()
                    ->whereHas('event', fn (Builder $event) => $event->withoutTrashed())
                    ->with('event')
                    ->latest()
                    ->latest('id')
                    ->paginate(15),
            ),
        ]);
    }
}
