<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\RegistrationBenefitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A benefit option chosen in a registration. The names and the price are copied,
 * so a later change of the option does not rewrite past registrations (ADR-022).
 *
 * @property int $id
 * @property int $registration_id
 * @property int $benefit_option_id
 * @property string $benefit_name
 * @property string $option_name
 * @property int $price gross price in grosze at the time of registering
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Registration $registration
 * @property-read BenefitOption $option
 */
#[Fillable(['benefit_name', 'option_name', 'price'])]
final class RegistrationBenefit extends Model
{
    /** @use HasFactory<RegistrationBenefitFactory> */
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
        ];
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<BenefitOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(BenefitOption::class, 'benefit_option_id');
    }
}
