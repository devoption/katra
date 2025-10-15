<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an admin user
        User::factory()->admin()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@katra.test',
        ]);

        // Create a regular user
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user@katra.test',
        ]);

        // Optionally create more test users
        // User::factory(10)->create();
    }
}
