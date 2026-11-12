<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\Consent;

final class DeleteConsent
{
    public function handle(Consent $consent): void
    {
        $consent->delete();
    }
}
