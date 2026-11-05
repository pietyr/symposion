<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventFormat;
use App\Enums\EventSort;
use App\Enums\Voivodeship;
use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string|null $invoice_title
 * @property string $slug
 * @property EventFormat $format
 * @property Voivodeship|null $voivodeship
 * @property string|null $city
 * @property string|null $venue
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property bool $is_visible
 * @property bool $is_promoted
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable(['title', 'invoice_title', 'format', 'voivodeship', 'city', 'venue', 'starts_at', 'ends_at', 'is_visible', 'is_promoted'])]
final class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'format' => EventFormat::class,
            'voivodeship' => Voivodeship::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_visible' => 'boolean',
            'is_promoted' => 'boolean',
        ];
    }

    /**
     * Online events take place nowhere, so any location left in the form is dropped.
     */
    public function forgetVenueIfOnline(): void
    {
        if ($this->format->hasVenue()) {
            return;
        }

        $this->voivodeship = null;
        $this->city = null;
        $this->venue = null;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * Events still in progress count as upcoming until they end.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('ends_at', '>=', now());
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function past(Builder $query): void
    {
        $query->where('ends_at', '<', now());
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function sortedBy(Builder $query, EventSort $sort): void
    {
        match ($sort) {
            EventSort::UpcomingThenPast => $query->upcomingThenPast(),
            EventSort::Upcoming => $query->upcoming()->oldest('starts_at'),
            EventSort::Past => $query->past()->latest('starts_at'),
            // Closest to today in either direction, so an event from last week beats one next year.
            EventSort::Nearest => $query->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, starts_at, ?))', [now()]),
            EventSort::Latest => $query->orderByDesc('starts_at'),
            EventSort::RecentlyAdded => $query->orderByDesc('created_at'),
        };
    }

    /**
     * Upcoming events first, nearest on top, then past events, most recent on top.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function upcomingThenPast(Builder $query): void
    {
        $now = now();

        $query
            ->orderByRaw('ends_at < ?', [$now])
            ->orderByRaw('CASE WHEN ends_at >= ? THEN starts_at END ASC', [$now])
            ->orderByRaw('CASE WHEN ends_at < ? THEN starts_at END DESC', [$now]);
    }

    /**
     * Match every word of the term anywhere in the title or the city. The collation already ignores
     * case and most Polish diacritics, but treats "ł" as a separate letter, so it is folded here.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function matchingSearch(Builder $query, string $term): void
    {
        foreach (Str::of($term)->squish()->explode(' ') as $word) {
            $pattern = '%'.addcslashes(str_replace(['ł', 'Ł'], 'l', $word), '%_\\').'%';

            $query->where(fn (Builder $wordQuery) => $wordQuery
                ->whereRaw("REPLACE(REPLACE(title, 'ł', 'l'), 'Ł', 'l') LIKE ?", [$pattern])
                ->orWhereRaw("REPLACE(REPLACE(city, 'ł', 'l'), 'Ł', 'l') LIKE ?", [$pattern]));
        }
    }
}
