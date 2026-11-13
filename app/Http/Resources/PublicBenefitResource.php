<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Benefit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A benefit field as the registration form shows it, without hidden options.
 *
 * @mixin Benefit
 */
final class PublicBenefitResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type->value,
            'is_required' => $this->is_required,
            'attendance' => $this->attendance?->value,
            'options' => PublicBenefitOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
