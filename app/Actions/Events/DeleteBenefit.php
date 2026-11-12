<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Benefit;

final class DeleteBenefit
{
    public function handle(Benefit $benefit): void
    {
        $benefit->delete();
    }
}
