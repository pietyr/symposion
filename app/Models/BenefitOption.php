<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BenefitOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One of the extras a participant can choose in a benefit field, e.g. "2 nights" of accommodation.
 *
 * @property int $id
 * @property int $benefit_id
 * @property string $name
 * @property int $price gross price in grosze, 0 when the option is included
 * @property int|null $seat_limit null means no limit
 * @property int $position
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Benefit $benefit
 */
#[Fillable(['name', 'price', 'seat_limit', 'position'])]
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
     * The options of the same benefit field, this one among them.
     *
     * @return Builder<self>
     */
    public function siblings(): Builder
    {
        return self::query()->where('benefit_id', $this->benefit_id);
    }
}
