<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\EventSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $event_id
 * @property string $title
 * @property string $content sanitized HTML
 * @property int $position
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read string $slug anchor of the section on the event page
 */
#[Fillable(['title', 'content', 'position'])]
final class EventSection extends Model
{
    /** @use HasFactory<EventSectionFactory> */
    use HasFactory;

    /**
     * Anchor of the event details, which always sit above the sections.
     */
    public const string DETAILS_ANCHOR = 'informacje';

    /**
     * @return Attribute<string, never>
     */
    protected function slug(): Attribute
    {
        return Attribute::get(fn (): string => Str::slug($this->title));
    }

    /**
     * The sections of the same event, this one among them.
     *
     * @return Builder<self>
     */
    public function siblings(): Builder
    {
        return self::query()->where('event_id', $this->event_id);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
