<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RegistrationBenefit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A chosen benefit option as it was when registering.
 *
 * @mixin RegistrationBenefit
 */
final class RegistrationBenefitResource extends JsonResource
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
            'benefit_name' => $this->benefit_name,
            'option_name' => $this->option_name,
            'price' => $this->price,
        ];
    }
}
