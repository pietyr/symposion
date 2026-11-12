<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\BenefitOption;

final class UpdateBenefitOption
{
    /**
     * @param  array{name: string, price: int, seat_limit: int|null}  $attributes
     */
    public function handle(BenefitOption $option, array $attributes): void
    {
        $option->update($attributes);
    }
}
