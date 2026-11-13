<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Benefit;
use Illuminate\Validation\ValidationException;

final class DeleteBenefit
{
    /**
     * A field with options chosen in registrations stays; hiding its options takes it out of the form (ADR-022).
     *
     * @throws ValidationException when a registration includes one of its options
     */
    public function handle(Benefit $benefit): void
    {
        if ($benefit->options()->whereHas('registrationBenefits')->exists()) {
            throw ValidationException::withMessages([
                'benefit' => __('This benefit is part of registrations. Hide its options instead of deleting it.'),
            ]);
        }

        $benefit->delete();
    }
}
