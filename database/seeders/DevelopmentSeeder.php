<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
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

        User::factory()->create([
            'name' => 'Jan Kowalski',
            'email' => 'user@example.com',
        ]);

        Event::factory()->visible()->count(8)->create();
        Event::factory()->visible()->promoted()->count(4)->create();
        Event::factory()->visible()->online()->count(5)->create();
        Event::factory()->visible()->hybrid()->count(5)->create();
        Event::factory()->visible()->past()->count(12)->create();
        Event::factory()->visible()->past()->online()->count(4)->create();
        Event::factory()->visible()->past()->hybrid()->count(4)->create();
        Event::factory()->count(3)->create();
        Event::factory()->visible()->count(2)->create()->each->delete();
    }
}
