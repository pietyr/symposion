<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Events;

use App\Enums\Attendance;
use App\Enums\Profession;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filters of an event's participant list; an unknown value is ignored rather than rejected.
 */
final class ListEventParticipantsRequest extends FormRequest
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
     * Arguments for the Registration::filteredBy() scope; without a status the list keeps
     * to the registrations that take a seat.
     *
     * @return array{status: RegistrationStatus|null, attendance: Attendance|null, profession: Profession|null, search: string}
     */
    public function participantFilters(): array
    {
        return [
            'status' => $this->enum('status', RegistrationStatus::class),
            'attendance' => $this->enum('attendance', Attendance::class),
            'profession' => $this->enum('profession', Profession::class),
            'search' => $this->string('search')->trim()->value(),
        ];
    }

    /**
     * The applied filters as the page's form fields show them.
     *
     * @return array{status: string, attendance: string, profession: string, search: string}
     */
    public function selectedFilters(): array
    {
        $filters = $this->participantFilters();

        return [
            'status' => $filters['status']->value ?? '',
            'attendance' => $filters['attendance']->value ?? '',
            'profession' => $filters['profession']->value ?? '',
            'search' => $filters['search'],
        ];
    }

    /**
     * Only the kinds of attendance the event offers are worth filtering by.
     *
     * @return array{statuses: list<array{value: string, label: string}>, attendances: list<array{value: string, label: string}>, professions: list<array{value: string, label: string}>}
     */
    public function filterOptions(Event $event): array
    {
        return [
            'statuses' => RegistrationStatus::options(),
            'attendances' => count($event->attendances()) > 1 ? array_map(
                fn (Attendance $attendance): array => ['value' => $attendance->value, 'label' => $attendance->label()],
                $event->attendances(),
            ) : [],
            'professions' => Profession::options(),
        ];
    }
}
