<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Actions\Registrations\DetermineRegistrationAvailability;
use App\Actions\Registrations\RegisterForEvent;
use App\Enums\Attendance;
use App\Enums\RegistrationAvailability;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\Events\RegisterForEventRequest;
use App\Http\Resources\PublicBenefitResource;
use App\Http\Resources\PublicEventResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class RegistrationController extends Controller
{
    /**
     * Show the registration form; when registration is not possible, the event page explains why.
     */
    public function create(Event $event, string $slug, #[CurrentUser] User $user, DetermineRegistrationAvailability $determineRegistrationAvailability): Response|RedirectResponse
    {
        if ($slug !== $event->slug) {
            return to_route('events.registration.create', ['event' => $event, 'slug' => $event->slug], 301);
        }

        $availability = $determineRegistrationAvailability->handle($event, $user);

        if ($availability !== RegistrationAvailability::Open) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $availability->message()]);

            return to_route('events.show', ['event' => $event, 'slug' => $event->slug]);
        }

        return Inertia::render('site/events/Register', [
            'event' => PublicEventResource::make($event),
            'attendances' => array_map(fn (Attendance $attendance): array => [
                'value' => $attendance->value,
                'label' => $attendance->label(),
                'price' => $event->priceFor($attendance) ?? 0,
                'seats_left' => $event->seatsLeft($attendance),
            ], $event->attendances()),
            'benefits' => PublicBenefitResource::collection($event->benefitsForRegistration()),
        ]);
    }

    /**
     * Register the signed-in participant.
     */
    public function store(RegisterForEventRequest $request, Event $event, #[CurrentUser] User $user, RegisterForEvent $registerForEvent): RedirectResponse
    {
        $registerForEvent->handle($event, $user, $request->attendance(), $request->benefitChoices());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('You are registered for the event.')]);

        return to_route('events.show', ['event' => $event, 'slug' => $event->slug]);
    }
}
