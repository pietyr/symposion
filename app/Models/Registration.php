<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A registration for an event, also its "payment": taking part in a free event costs 0 (ADR-019).
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property Attendance $attendance
 * @property int $amount gross amount in grosze, copied from the event price when registering
 * @property RegistrationStatus $status
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Event $event
 * @property-read User $user
 */
#[Fillable(['attendance', 'amount', 'status', 'confirmed_at'])]
final class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance' => Attendance::class,
            'amount' => 'integer',
            'status' => RegistrationStatus::class,
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * Deleted events keep their registrations, which the admin still sees.
     *
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The registration number in the title lets the administrator match the transfer.
     */
    public function transferTitle(): string
    {
        return __('Registration no. :number', ['number' => $this->id]);
    }

    /**
     * Narrow the list down by whichever filters are set; the search goes through User::matchingSearch().
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function filteredBy(
        Builder $query,
        ?int $eventId = null,
        ?RegistrationStatus $status = null,
        ?Attendance $attendance = null,
        string $search = '',
    ): void {
        $query
            ->when($eventId, fn (Builder $query, int $eventId) => $query->where('event_id', $eventId))
            ->when($status, fn (Builder $query, RegistrationStatus $status) => $query->where('status', $status))
            ->when($attendance, fn (Builder $query, Attendance $attendance) => $query->where('attendance', $attendance))
            ->when($search !== '', fn (Builder $query) => $query->whereHas(
                'user',
                fn (Builder $user) => $user->matchingSearch($search),
            ));
    }
}
