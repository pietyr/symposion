<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Profession;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A user with the professional profile and the account state, as the admin sees them.
 *
 * @mixin User
 */
final class ParticipantResource extends JsonResource
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
            'academic_title' => $this->academic_title,
            'name' => $this->name,
            'email' => $this->email,
            'profession_label' => Profession::tryFrom((string) $this->profession)?->label(),
            'license_number' => $this->license_number,
            'university' => $this->university,
            'student_number' => $this->student_number,
            'company' => $this->company,
            'profession_name' => $this->profession_name,
            'specializations' => $this->specializations
                ->sortBy('name')
                ->map(fn (Specialization $specialization): string => $specialization->name)
                ->values()
                ->all(),
            'is_admin' => $this->isAdmin(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'blocked_at' => $this->blocked_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
