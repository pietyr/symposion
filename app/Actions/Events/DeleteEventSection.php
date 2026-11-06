<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Models\EventSection;

final class DeleteEventSection
{
    public function handle(EventSection $section): void
    {
        $section->delete();
    }
}
