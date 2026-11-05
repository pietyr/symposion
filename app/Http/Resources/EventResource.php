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
            'slug' => $this->slug,
            'format' => $this->format->value,
            'format_label' => $this->format->label(),
            'voivodeship' => $this->voivodeship?->value,
            'city' => $this->city,
            'venue' => $this->venue,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'is_visible' => $this->is_visible,
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
