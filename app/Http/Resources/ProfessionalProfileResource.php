<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The professional fields of the signed-in user, sent only to the profile settings page.
 *
 * @mixin User
 */
final class ProfessionalProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'profession' => $this->profession,
            'license_number' => $this->license_number,
            'university' => $this->university,
            'student_number' => $this->student_number,
            'company' => $this->company,
            'profession_name' => $this->profession_name,
            'specializations' => $this->specializations
                ->map(fn (Specialization $specialization): string => (string) $specialization->id)
                ->values()
                ->all(),
        ];
    }
}
