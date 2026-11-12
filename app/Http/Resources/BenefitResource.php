<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Benefit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Benefit
 */
final class BenefitResource extends JsonResource
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
            'type_label' => $this->type->label(),
            'is_required' => $this->is_required,
            'attendance' => $this->attendance?->value,
            'attendance_label' => $this->attendance?->onlyLabel(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
