<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Attendance;
use App\Enums\EventFormat;
use App\Enums\EventPriceFilter;
use App\Enums\EventSort;
use App\Enums\Profession;
use App\Enums\RegistrationStatus;
use App\Enums\Voivodeship;
use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
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
 * @property CarbonImmutable|null $registration_opens_at without it registration opens right away
 * @property CarbonImmutable|null $registration_closes_at without it registration lasts until the event starts
 * @property int|null $price_online gross amount in grosze; 0 means free
 * @property int|null $price_in_person gross amount in grosze; 0 means free
 * @property int|null $seats_online seat limit for online attendance; null means no limit
 * @property int|null $seats_in_person seat limit at the venue; null means no limit
 * @property bool $is_visible
 * @property bool $is_promoted
 * @property string|null $primary_color hex, e.g. #0f766e
 * @property string|null $accent_color hex, e.g. #f59e0b
 * @property string|null $large_banner_path on the public disk
 * @property string|null $small_banner_path on the public disk
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, EventProfession> $professionEntries
 * @property-read list<Profession> $professions
 * @property-read Collection<int, Specialization> $specializations
 * @property-read Collection<int, EventSection> $sections
 * @property-read Collection<int, Registration> $registrations
 * @property-read string|null $large_banner_url
 * @property-read string|null $small_banner_url
 * @property-read int $default_banner number of the bundled banner pair
 */
#[Fillable(['title', 'invoice_title', 'format', 'voivodeship', 'city', 'venue', 'starts_at', 'ends_at', 'registration_opens_at', 'registration_closes_at', 'price_online', 'price_in_person', 'seats_online', 'seats_in_person', 'is_visible', 'is_promoted', 'primary_color', 'accent_color', 'large_banner_path', 'small_banner_path'])]
final class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    /**
     * How many banner and thumbnail pairs are bundled in public/images/event-banners.
     */
    private const int DEFAULT_BANNER_PAIRS = 9;

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
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'price_online' => 'integer',
            'price_in_person' => 'integer',
            'seats_online' => 'integer',
            'seats_in_person' => 'integer',
            'is_visible' => 'boolean',
            'is_promoted' => 'boolean',
        ];
    }

    /**
     * @return HasMany<EventProfession, $this>
     */
    public function professionEntries(): HasMany
    {
        return $this->hasMany(EventProfession::class);
    }

    /**
     * @return BelongsToMany<Specialization, $this>
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Ways of taking part the format offers.
     *
     * @return list<Attendance>
     */
    public function attendances(): array
    {
        return array_values(array_filter([
            $this->format->hasOnlineAttendance() ? Attendance::Online : null,
            $this->format->hasVenue() ? Attendance::InPerson : null,
        ]));
    }

    /**
     * Seat limit for the way of taking part; null means no limit.
     */
    public function seatLimit(Attendance $attendance): ?int
    {
        return match ($attendance) {
            Attendance::Online => $this->seats_online,
            Attendance::InPerson => $this->seats_in_person,
        };
    }

    /**
     * Free seats, counting pending and paid registrations (ADR-019); null when there is no limit.
     */
    public function seatsLeft(Attendance $attendance): ?int
    {
        $limit = $this->seatLimit($attendance);

        if ($limit === null) {
            return null;
        }

        $taken = $this->registrations()
            ->where('attendance', $attendance)
            ->whereIn('status', RegistrationStatus::seatTaking())
            ->count();

        return max(0, $limit - $taken);
    }

    /**
     * Without a closing date registration lasts until the event starts.
     */
    public function registrationClosesAt(): CarbonImmutable
    {
        return $this->registration_closes_at ?? $this->starts_at;
    }

    /**
     * An event without professions is open to all of them.
     */
    public function isOpenTo(Profession $profession): bool
    {
        $entries = $this->professionEntries();

        return ! $entries->exists() || $entries->where('profession', $profession->value)->exists();
    }

    /**
     * The pending or paid registration of the user, if there is one.
     */
    public function activeRegistrationOf(User $user): ?Registration
    {
        return $this->registrations()
            ->whereBelongsTo($user)
            ->whereIn('status', RegistrationStatus::seatTaking())
            ->latest()
            ->first();
    }

    /**
     * A cancelled registration does not stop the participant from registering again.
     */
    public function hasActiveRegistrationOf(User $user): bool
    {
        return $this->registrations()
            ->whereBelongsTo($user)
            ->whereIn('status', RegistrationStatus::seatTaking())
            ->exists();
    }

    /**
     * Gross price in grosze for the way of taking part; null when the format does not offer it.
     */
    public function priceFor(Attendance $attendance): ?int
    {
        return match ($attendance) {
            Attendance::Online => $this->format->hasOnlineAttendance() ? $this->price_online : null,
            Attendance::InPerson => $this->format->hasVenue() ? $this->price_in_person : null,
        };
    }

    /**
     * Content sections in the order they appear on the event page.
     *
     * @return HasMany<EventSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(EventSection::class)->orderBy('position');
    }

    /**
     * Professions the event is open to; values the enum no longer knows are skipped.
     *
     * @return Attribute<list<Profession>, never>
     */
    protected function professions(): Attribute
    {
        return Attribute::get(function (): array {
            $professions = [];

            foreach ($this->professionEntries as $entry) {
                $profession = Profession::tryFrom($entry->profession);

                if ($profession !== null) {
                    $professions[] = $profession;
                }
            }

            return $professions;
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function largeBannerUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->bannerUrl($this->large_banner_path));
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function smallBannerUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->bannerUrl($this->small_banner_path));
    }

    /**
     * Derived from the id, so an event keeps its picture and consecutive events differ.
     *
     * @return Attribute<int, never>
     */
    protected function defaultBanner(): Attribute
    {
        return Attribute::get(fn (): int => $this->defaultBannerNumber());
    }

    private function defaultBannerNumber(): int
    {
        return ($this->id - 1) % self::DEFAULT_BANNER_PAIRS + 1;
    }

    private function bannerUrl(?string $path): ?string
    {
        return $path === null ? null : Storage::disk('public')->url($path);
    }

    /**
     * Drop what the format does not use, such as a venue left in the form of an online event.
     */
    public function forgetFieldsOutsideFormat(): void
    {
        if (! $this->format->hasVenue()) {
            $this->voivodeship = null;
            $this->city = null;
            $this->venue = null;
            $this->price_in_person = null;
            $this->seats_in_person = null;
        }

        if (! $this->format->hasOnlineAttendance()) {
            $this->price_online = null;
            $this->seats_online = null;
        }
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
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function promoted(Builder $query): void
    {
        $query->where('is_promoted', true);
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
     * Narrow the query down by whichever filters are set.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function filteredBy(
        Builder $query,
        ?EventFormat $format = null,
        ?Profession $profession = null,
        ?Voivodeship $voivodeship = null,
        ?EventPriceFilter $price = null,
        ?bool $isVisible = null,
        string $search = '',
    ): void {
        $query
            ->when($format, fn (Builder $query, EventFormat $format) => $query->where('format', $format))
            ->when($voivodeship, fn (Builder $query, Voivodeship $voivodeship) => $query->where('voivodeship', $voivodeship))
            ->when($profession, fn (Builder $query, Profession $profession) => $query->whereHas(
                'professionEntries',
                fn (Builder $entries) => $entries->where('profession', $profession->value),
            ))
            // A hybrid event can be both: free online and paid in person.
            ->when($price === EventPriceFilter::Free, fn (Builder $query) => $query->where(
                fn (Builder $prices) => $prices->where('price_online', 0)->orWhere('price_in_person', 0),
            ))
            ->when($price === EventPriceFilter::Paid, fn (Builder $query) => $query->where(
                fn (Builder $prices) => $prices->where('price_online', '>', 0)->orWhere('price_in_person', '>', 0),
            ))
            ->when($isVisible !== null, fn (Builder $query) => $query->where('is_visible', $isVisible))
            ->when($search !== '', fn (Builder $query) => $query->matchingSearch($search));
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
