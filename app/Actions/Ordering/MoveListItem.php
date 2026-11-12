<?php

declare(strict_types=1);

namespace App\Actions\Ordering;

use App\Enums\MoveDirection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class MoveListItem
{
    /**
     * Swap the item's position with its neighbour among the siblings; at the edge of the list
     * nothing changes. Moving does not edit the item, so updated_at stays as it is.
     *
     * @template TModel of Model
     *
     * @param  TModel  $item
     * @param  Builder<TModel>  $siblings  the items of the same list, the moved one among them
     */
    public function handle(Model $item, Builder $siblings, MoveDirection $direction): void
    {
        $position = $item->getAttribute('position');

        $neighbour = $siblings
            ->where('position', $direction === MoveDirection::Up ? '<' : '>', $position)
            ->reorder('position', $direction === MoveDirection::Up ? 'desc' : 'asc')
            ->first();

        if ($neighbour === null) {
            return;
        }

        $item::withoutTimestamps(fn () => DB::transaction(function () use ($item, $neighbour, $position): void {
            $item->setAttribute('position', $neighbour->getAttribute('position'));
            $neighbour->setAttribute('position', $position);

            $item->save();
            $neighbour->save();
        }));
    }
}
