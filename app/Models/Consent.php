<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ConsentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A consent the participant gives in the registration form, e.g. to the terms of the event (ADR-021).
 *
 * @property int $id
 * @property int $event_id
 * @property string $content sanitized HTML, so it can link to the terms
 * @property bool $is_required a required consent has to be given to register
 * @property int $position
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Event $event
 */
#[Fillable(['content', 'is_required', 'position'])]
final class Consent extends Model
{
    /** @use HasFactory<ConsentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<RegistrationConsent, $this>
     */
    public function registrationConsents(): HasMany
    {
        return $this->hasMany(RegistrationConsent::class);
    }

    /**
     * The consents of the same event, this one among them.
     *
     * @return Builder<self>
     */
    public function siblings(): Builder
    {
        return self::query()->where('event_id', $this->event_id);
    }
}
