<?php

declare(strict_types=1);

namespace App\Actions\Specializations;

use App\Models\Specialization;

final class UpdateSpecialization
{
    public function handle(Specialization $specialization, string $name): void
    {
        $specialization->update(['name' => $name]);
    }
}
