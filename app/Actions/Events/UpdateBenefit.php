<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Models\Benefit;

final class UpdateBenefit
{
    /**
     * @param  array{name: string, type: BenefitType, is_required: bool, attendance: Attendance|null}  $attributes
     */
    public function handle(Benefit $benefit, array $attributes): void
    {
        $benefit->update($attributes);
    }
}
