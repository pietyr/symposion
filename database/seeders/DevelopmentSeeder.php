<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Attendance;
use App\Enums\BenefitType;
use App\Enums\EventFormat;
use App\Enums\Profession;
use App\Enums\RegistrationStatus;
use App\Models\Benefit;
use App\Models\BenefitOption;
use App\Models\Consent;
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
        $this->seedBenefitsAndConsents();
    }

    /**
     * Benefit fields and consents on the upcoming events, as the registration form will show them.
     */
    private function seedBenefitsAndConsents(): void
    {
        foreach (Event::query()->upcoming()->get() as $event) {
            if ($event->format->hasVenue()) {
                $this->seedVenueBenefits($event);
            } else {
                $this->seedOnlineBenefits($event);
            }

            Consent::factory()->for($event)->required()->create([
                'content' => '<p>Akceptuję <a href="https://example.com/regulamin">regulamin wydarzenia</a>.</p>',
                'position' => 1,
            ]);
            Consent::factory()->for($event)->create([
                'content' => '<p>Wyrażam zgodę na publikację mojego wizerunku na zdjęciach z wydarzenia.</p>',
                'position' => 2,
            ]);
        }
    }

    /**
     * Accommodation and a dinner concern only those who come, so at a hybrid event they are limited to them.
     */
    private function seedVenueBenefits(Event $event): void
    {
        $inPersonOnly = $event->format === EventFormat::Hybrid ? Attendance::InPerson : null;

        $accommodation = Benefit::factory()->for($event)->select()->create(['name' => 'Nocleg', 'attendance' => $inPersonOnly, 'position' => 1]);
        BenefitOption::factory()->for($accommodation)->createMany([
            ['name' => '1 noc', 'price' => 30000, 'seat_limit' => 40, 'position' => 1],
            ['name' => '2 noce', 'price' => 55000, 'seat_limit' => 40, 'position' => 2],
            ['name' => '3 noce', 'price' => 80000, 'seat_limit' => 10, 'is_disabled' => true, 'position' => 3],
        ]);

        $dinner = Benefit::factory()->for($event)->create(['name' => 'Kolacja galowa', 'attendance' => $inPersonOnly, 'position' => 2]);
        BenefitOption::factory()->for($dinner)->create(['name' => 'Udział w kolacji', 'price' => 18000, 'seat_limit' => 80, 'position' => 1]);

        $workshops = Benefit::factory()->for($event)->create(['name' => 'Warsztaty', 'type' => BenefitType::Radio, 'is_required' => true, 'position' => 3]);
        BenefitOption::factory()->for($workshops)->createMany([
            ['name' => 'Interpretacja EKG', 'price' => 0, 'seat_limit' => 20, 'position' => 1],
            ['name' => 'USG w praktyce', 'price' => 0, 'seat_limit' => 20, 'position' => 2],
            ['name' => 'Resuscytacja krążeniowo-oddechowa', 'price' => 0, 'seat_limit' => 15, 'is_hidden' => true, 'position' => 3],
        ]);
    }

    private function seedOnlineBenefits(Event $event): void
    {
        $recordings = Benefit::factory()->for($event)->create(['name' => 'Nagrania wykładów', 'position' => 1]);
        BenefitOption::factory()->for($recordings)->create(['name' => 'Dostęp do nagrań przez 30 dni', 'price' => 9900, 'position' => 1]);
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
