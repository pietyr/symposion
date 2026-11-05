<?php

declare(strict_types=1);

namespace App\Actions\Specializations;

use App\Models\Specialization;

final class DeleteSpecialization
{
    public function handle(Specialization $specialization): void
    {
        $specialization->delete();
    }
}
