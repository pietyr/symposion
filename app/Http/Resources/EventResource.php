<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Event;
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
            'price_online' => $this->price_online,
            'price_in_person' => $this->price_in_person,
            'is_visible' => $this->is_visible,
            'is_promoted' => $this->is_promoted,
            'is_past' => $this->ends_at->isPast(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
