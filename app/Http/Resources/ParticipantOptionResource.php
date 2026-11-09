<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A user as an option of the participant search.
 *
 * @mixin User
 */
final class ParticipantOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{value: string, label: string, description: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'value' => (string) $this->id,
            'label' => $this->name,
            'description' => $this->email,
        ];
    }
}
