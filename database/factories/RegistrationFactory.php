<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Attendance;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
final class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'attendance' => Attendance::InPerson,
            'amount' => 25000,
            'status' => RegistrationStatus::Pending,
            'confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the payment has been confirmed.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Paid,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the registration was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Cancelled,
        ]);
    }

    /**
     * Indicate that the participant attends online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'attendance' => Attendance::Online,
        ]);
    }
}
