<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BenefitOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BenefitOption
 */
final class BenefitOptionResource extends JsonResource
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
            'price' => $this->price,
            'seat_limit' => $this->seat_limit,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
