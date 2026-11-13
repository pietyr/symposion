<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\RegistrationConsentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A consent the participant gave when registering. The content is copied, so a later
 * edit of the consent does not change what the participant agreed to (ADR-022).
 *
 * @property int $id
 * @property int $registration_id
 * @property int $consent_id
 * @property string $content sanitized HTML as it read when the consent was given
 * @property CarbonImmutable|null $created_at when the consent was given
 * @property CarbonImmutable|null $updated_at
 * @property-read Registration $registration
 * @property-read Consent $consent
 */
#[Fillable(['content'])]
final class RegistrationConsent extends Model
{
    /** @use HasFactory<RegistrationConsentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<Consent, $this>
     */
    public function consent(): BelongsTo
    {
        return $this->belongsTo(Consent::class);
    }
}
