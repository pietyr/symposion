<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\BenefitOption;
use Illuminate\Validation\ValidationException;

final class DeleteBenefitOption
{
    /**
     * An option chosen in registrations stays; hiding it takes it out of the form (ADR-022).
     *
     * @throws ValidationException when a registration includes the option
     */
    public function handle(BenefitOption $option): void
    {
        if ($option->registrationBenefits()->exists()) {
            throw ValidationException::withMessages([
                'option' => __('This option is part of registrations. Hide it instead of deleting it.'),
            ]);
        }

        $option->delete();
    }
}
