<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Registrations;

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filters of the admin registration list; an unknown value is ignored rather than rejected.
 */
final class ListRegistrationsRequest extends FormRequest
{
    /**
     * Authorization happens in the controller through the registration policy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Arguments for the Registration::filteredBy() scope.
     *
     * @return array{eventId: int|null, status: RegistrationStatus|null, attendance: Attendance|null, search: string}
     */
    public function registrationFilters(): array
    {
        $eventId = $this->integer('event');

        return [
            'eventId' => $eventId > 0 ? $eventId : null,
            'status' => $this->enum('status', RegistrationStatus::class),
            'attendance' => $this->enum('attendance', Attendance::class),
            'search' => $this->string('search')->trim()->value(),
        ];
    }

    /**
     * The applied filters as the page's form fields show them.
     *
     * @return array{event: string, status: string, attendance: string, search: string}
     */
    public function selectedFilters(): array
    {
        $filters = $this->registrationFilters();

        return [
            'event' => $filters['eventId'] === null ? '' : (string) $filters['eventId'],
            'status' => $filters['status']->value ?? '',
            'attendance' => $filters['attendance']->value ?? '',
            'search' => $filters['search'],
        ];
    }

    /**
     * Events newest first, deleted ones marked, because their registrations stay on the list.
     *
     * @return array{events: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>, attendances: list<array{value: string, label: string}>}
     */
    public function filterOptions(): array
    {
        return [
            'events' => array_values(Event::withTrashed()
                ->latest('starts_at')
                ->get(['id', 'title', 'deleted_at'])
                ->map(fn (Event $event): array => [
                    'value' => (string) $event->id,
                    'label' => $event->trashed() ? __(':title (deleted)', ['title' => $event->title]) : $event->title,
                ])
                ->all()),
            'statuses' => RegistrationStatus::options(),
            'attendances' => Attendance::options(),
        ];
    }
}
