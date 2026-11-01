<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DevelopmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed ready-to-use local accounts (password: "password").
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
    }
}
