<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Benefit;
use App\Models\BenefitOption;

final class CreateBenefitOption
{
    /**
     * Add an option at the end of the benefit field.
     *
     * @param  array{name: string, price: int, seat_limit: int|null}  $attributes
     */
    public function handle(Benefit $benefit, array $attributes): BenefitOption
    {
        return $benefit->options()->create([
            ...$attributes,
            'position' => ($benefit->options()->reorder('position', 'desc')->first()->position ?? 0) + 1,
        ]);
    }
}
