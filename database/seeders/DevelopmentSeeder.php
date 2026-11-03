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

        Event::factory()->published()->count(6)->create();
        Event::factory()->published()->online()->count(3)->create();
        Event::factory()->published()->hybrid()->count(3)->create();
        Event::factory()->published()->past()->count(4)->create();
        Event::factory()->count(2)->create();
        Event::factory()->published()->create()->delete();
    }
}
