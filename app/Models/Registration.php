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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

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
 * @property-read Collection<int, RegistrationBenefit> $benefits
 */
#[Fillable(['attendance', 'amount', 'status', 'confirmed_at'])]
final class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /**
     * The longest transfer title Polish banks accept.
     */
    private const int TRANSFER_TITLE_LENGTH = 140;

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
     * The benefit options chosen when registering.
     *
     * @return HasMany<RegistrationBenefit, $this>
     */
    public function benefits(): HasMany
    {
        return $this->hasMany(RegistrationBenefit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * "Jan Kowalski, Kongres Kardiologii, 2027, Kraków" lets the administrator match the transfer.
     * Banks take up to 140 characters, so a long event title is shortened to fit.
     */
    public function transferTitle(): string
    {
        $place = $this->attendance === Attendance::Online ? __('Online') : ($this->event->city ?? __('Online'));
        $before = $this->user->name.', ';
        $after = ', '.$this->event->starts_at->year.', '.$place;

        $room = self::TRANSFER_TITLE_LENGTH - mb_strlen($before.$after);
        $title = mb_strlen($this->event->title) <= $room
            ? $this->event->title
            : Str::limit($this->event->title, $room - 1, '…');

        return $before.$title.$after;
    }

    /**
     * What the participant needs to pay by transfer; null when nothing is owed.
     *
     * @return array{recipient: string, account_number: string, title: string, amount: int}|null
     */
    public function transferDetails(): ?array
    {
        if ($this->status !== RegistrationStatus::Pending || $this->amount === 0) {
            return null;
        }

        return [
            'recipient' => Config::string('symposion.bank_transfer.recipient'),
            'account_number' => Config::string('symposion.bank_transfer.account_number'),
            'title' => $this->transferTitle(),
            'amount' => $this->amount,
        ];
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
