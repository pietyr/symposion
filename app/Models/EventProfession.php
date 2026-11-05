<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EventProfessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $profession raw value, deliberately not cast to the enum (see Profession)
 */
#[Fillable(['profession'])]
final class EventProfession extends Model
{
    /** @use HasFactory<EventProfessionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
