<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Attendance;
use App\Enums\BenefitType;
use Carbon\CarbonImmutable;
use Database\Factories\BenefitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A field of the registration form with extras to choose, such as accommodation or a dinner (ADR-021).
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property BenefitType $type
 * @property bool $is_required only single choice and a drop-down list can require a choice
 * @property Attendance|null $attendance null when every participant sees the field
 * @property int $position
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Event $event
 */
#[Fillable(['name', 'type', 'is_required', 'attendance', 'position'])]
final class Benefit extends Model
{
    /** @use HasFactory<BenefitFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BenefitType::class,
            'is_required' => 'boolean',
            'attendance' => Attendance::class,
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The benefit fields of the same event, this one among them.
     *
     * @return Builder<self>
     */
    public function siblings(): Builder
    {
        return self::query()->where('event_id', $this->event_id);
    }
}
