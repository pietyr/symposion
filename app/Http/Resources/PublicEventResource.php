<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Profession;
use App\Models\Event;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Event data for visitors: leaves out administrative fields such as the voivodeship.
 *
 * @mixin Event
 */
final class PublicEventResource extends JsonResource
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
            'slug' => $this->slug,
            'format_label' => $this->format->label(),
            'city' => $this->city,
            'venue' => $this->venue,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'price_online' => $this->price_online,
            'price_in_person' => $this->price_in_person,
            'professions' => $this->whenLoaded('professionEntries', fn (): array => array_map(
                fn (Profession $profession): string => $profession->label(),
                $this->professions,
            )),
            'specializations' => $this->whenLoaded('specializations', fn (): array => $this->specializations
                ->map(fn (Specialization $specialization): string => $specialization->name)
                ->values()
                ->all()),
        ];
    }
}
