<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Profession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A user account as the administrator sees it on the user list.
 *
 * @mixin User
 */
final class AdminUserResource extends JsonResource
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
            'email' => $this->email,
            'profession_label' => Profession::tryFrom((string) $this->profession)?->label(),
            'is_admin' => $this->isAdmin(),
            'is_verified' => $this->email_verified_at !== null,
            'is_blocked' => $this->isBlocked(),
            'registrations_count' => $this->whenCounted('registrations'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
