<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BenefitOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BenefitOption
 */
final class PublicBenefitOptionResource extends JsonResource
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
            'is_disabled' => $this->is_disabled,
            'seats_left' => $this->seatsLeft(),
        ];
    }
}
