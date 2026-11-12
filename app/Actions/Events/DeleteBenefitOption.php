<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\BenefitOption;

final class DeleteBenefitOption
{
    public function handle(BenefitOption $option): void
    {
        $option->delete();
    }
}
