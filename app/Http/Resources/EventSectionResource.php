<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EventSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventSection
 */
final class EventSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, title: string, slug: string, content: string, updated_at: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
