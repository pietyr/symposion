<?php

declare(strict_types=1);

namespace App\Actions\Events;

use App\Enums\MoveDirection;
use App\Models\EventSection;
use Illuminate\Support\Facades\DB;

final class MoveEventSection
{
    /**
     * Swap the section with its neighbour; at the edge of the page nothing changes.
     * Moving does not edit the content, so updated_at stays as it is.
     */
    public function handle(EventSection $section, MoveDirection $direction): void
    {
        $neighbour = EventSection::query()
            ->where('event_id', $section->event_id)
            ->where('position', $direction === MoveDirection::Up ? '<' : '>', $section->position)
            ->orderBy('position', $direction === MoveDirection::Up ? 'desc' : 'asc')
            ->first();

        if ($neighbour === null) {
            return;
        }

        EventSection::withoutTimestamps(fn () => DB::transaction(function () use ($section, $neighbour): void {
            [$section->position, $neighbour->position] = [$neighbour->position, $section->position];

            $section->save();
            $neighbour->save();
        }));
    }
}
