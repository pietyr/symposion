<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A participant's own registration, with what is needed to pay it by transfer.
 *
 * @mixin Registration
 */
final class MyRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $awaitsTransfer = $this->status === RegistrationStatus::Pending && $this->amount > 0;

        return [
            'id' => $this->id,
            'event' => $this->whenLoaded('event', fn (): array => [
                'id' => $this->event->id,
                'slug' => $this->event->slug,
                'title' => $this->event->title,
                'starts_at' => $this->event->starts_at->toIso8601String(),
                'ends_at' => $this->event->ends_at->toIso8601String(),
            ]),
            'attendance_label' => $this->attendance->label(),
            'amount' => $this->amount,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'created_at' => $this->created_at?->toIso8601String(),
            'transfer' => $awaitsTransfer ? [
                'recipient' => config('symposion.bank_transfer.recipient'),
                'account_number' => config('symposion.bank_transfer.account_number'),
                'title' => $this->transferTitle(),
                'amount' => $this->amount,
            ] : null,
        ];
    }
}
