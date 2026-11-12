<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Profession;
use App\Models\Event;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
final class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'invoice_title' => $this->invoice_title,
            'slug' => $this->slug,
            'format' => $this->format->value,
            'format_label' => $this->format->label(),
            'voivodeship' => $this->voivodeship?->value,
            'city' => $this->city,
            'venue' => $this->venue,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'registration_opens_at' => $this->registration_opens_at?->toIso8601String(),
            'registration_closes_at' => $this->registration_closes_at?->toIso8601String(),
            'price_online' => $this->price_online,
            'price_in_person' => $this->price_in_person,
            'seats_online' => $this->seats_online,
            'seats_in_person' => $this->seats_in_person,
            'registrations_count' => $this->whenCounted('registrations'),
            'professions' => $this->whenLoaded('professionEntries', fn (): array => array_map(
                fn (Profession $profession): string => $profession->value,
                $this->professions,
            )),
            'specializations' => $this->whenLoaded('specializations', fn (): array => $this->specializations
                ->map(fn (Specialization $specialization): string => (string) $specialization->id)
                ->values()
                ->all()),
            'sections' => EventSectionResource::collection($this->whenLoaded('sections')),
            'benefits' => BenefitResource::collection($this->whenLoaded('benefits')),
            'is_visible' => $this->is_visible,
            'is_promoted' => $this->is_promoted,
            'primary_color' => $this->primary_color,
            'accent_color' => $this->accent_color,
            'large_banner_url' => $this->large_banner_url,
            'small_banner_url' => $this->small_banner_url,
            'is_past' => $this->ends_at->isPast(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
