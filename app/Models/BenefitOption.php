<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\BenefitOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One of the extras a participant can choose in a benefit field, e.g. "2 nights" of accommodation.
 *
 * @property int $id
 * @property int $benefit_id
 * @property string $name
 * @property int $price gross price in grosze, 0 when the option is included
 * @property int|null $seat_limit null means no limit
 * @property bool $is_disabled shown in the registration form, but cannot be chosen
 * @property bool $is_hidden left out of the registration form
 * @property int $position
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Benefit $benefit
 * @property-read int|null $taken_seats counted by withTakenSeats()
 */
#[Fillable(['name', 'price', 'seat_limit', 'is_disabled', 'is_hidden', 'position'])]
final class BenefitOption extends Model
{
    /** @use HasFactory<BenefitOptionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'seat_limit' => 'integer',
            'is_disabled' => 'boolean',
            'is_hidden' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Benefit, $this>
     */
    public function benefit(): BelongsTo
    {
        return $this->belongsTo(Benefit::class);
    }

    /**
     * @return HasMany<RegistrationBenefit, $this>
     */
    public function registrationBenefits(): HasMany
    {
        return $this->hasMany(RegistrationBenefit::class);
    }

    /**
     * Free seats, counting pending and paid registrations (ADR-022); null when there is no limit.
     * The count from withTakenSeats() saves a query per option when a whole form is shown.
     */
    public function seatsLeft(): ?int
    {
        if ($this->seat_limit === null) {
            return null;
        }

        $taken = $this->taken_seats ?? $this->registrationBenefits()
            ->whereHas('registration', fn (Builder $registration) => $registration->whereIn('status', RegistrationStatus::seatTaking()))
            ->count();

        return max(0, $this->seat_limit - $taken);
    }

    /**
     * Whether a participant can pick the option now.
     */
    public function isAvailable(): bool
    {
        return ! $this->is_hidden && ! $this->is_disabled && $this->seatsLeft() !== 0;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withTakenSeats(Builder $query): void
    {
        $query->withCount([
            'registrationBenefits as taken_seats' => fn (Builder $chosen) => $chosen->whereHas(
                'registration',
                fn (Builder $registration) => $registration->whereIn('status', RegistrationStatus::seatTaking()),
            ),
        ]);
    }

    /**
     * The options of the same benefit field, this one among them.
     *
     * @return Builder<self>
     */
    public function siblings(): Builder
    {
        return self::query()->where('benefit_id', $this->benefit_id);
    }
}
