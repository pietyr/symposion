<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Profession;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DevelopmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed ready-to-use local accounts (password: "password") and sample events.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->doctor()->create([
            'name' => 'Jan Kowalski',
            'email' => 'user@example.com',
        ])->specializations()->attach(Specialization::query()->inRandomOrder()->limit(2)->pluck('id'));

        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->count(8)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->promoted()->count(4)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->online()->count(5)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->hybrid()->count(5)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->past()->count(12)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->past()->online()->count(4)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->past()->hybrid()->count(4)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->count(3)->create();
        Event::factory()->withProfessions()->withSpecializations()->withSections()->withBrandColors()->visible()->count(2)->create()->each->delete();

        $this->seedRegistrations();
    }

    /**
     * A few participants on most events, including deleted ones, whose registrations the admin still sees.
     */
    private function seedRegistrations(): void
    {
        $participants = User::factory()->doctor()->count(15)->create()
            ->concat(User::factory()->count(15)->create(['profession' => Profession::LaboratoryTechnician->value]));

        foreach (Event::withTrashed()->get() as $event) {
            foreach ($participants->random(fake()->numberBetween(0, 8)) as $participant) {
                $attendance = collect($event->attendances())->random();
                $amount = $event->priceFor($attendance) ?? 0;
                $status = $amount === 0
                    ? RegistrationStatus::Paid
                    : collect([RegistrationStatus::Paid, RegistrationStatus::Paid, RegistrationStatus::Pending, RegistrationStatus::Cancelled])->random();

                Registration::factory()->for($event)->for($participant)->create([
                    'attendance' => $attendance,
                    'amount' => $amount,
                    'status' => $status,
                    'confirmed_at' => $status === RegistrationStatus::Paid ? now() : null,
                    'created_at' => fake()->dateTimeBetween('-30 days'),
                ]);
            }
        }
    }
}
