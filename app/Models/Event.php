<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventFormat;
use App\Enums\Voivodeship;
use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property EventFormat $format
 * @property Voivodeship|null $voivodeship
 * @property string|null $city
 * @property string|null $venue
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property bool $is_visible
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable(['title', 'format', 'voivodeship', 'city', 'venue', 'starts_at', 'ends_at', 'is_visible'])]
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
    protected function titleContains(Builder $query, string $term): void
    {
        $query->whereLike('title', '%'.addcslashes($term, '%_\\').'%');
    }
}
