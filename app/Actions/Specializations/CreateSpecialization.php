<?php

declare(strict_types=1);

namespace App\Actions\Specializations;

use App\Models\Specialization;

final class CreateSpecialization
{
    public function handle(string $name): Specialization
    {
        return Specialization::create(['name' => $name]);
    }
}
