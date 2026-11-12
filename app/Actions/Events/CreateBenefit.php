<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Models\Benefit;
use App\Models\Event;

final class CreateBenefit
{
    /**
     * Add a benefit field at the end of the event's registration form.
     *
     * @param  array{name: string, type: BenefitType, is_required: bool, attendance: Attendance|null}  $attributes
     */
    public function handle(Event $event, array $attributes): Benefit
    {
        return $event->benefits()->create([
            ...$attributes,
            'position' => ($event->benefits()->reorder('position', 'desc')->first()->position ?? 0) + 1,
        ]);
    }
}
