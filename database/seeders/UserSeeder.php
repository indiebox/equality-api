<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create basic users.
        $user1 = User::firstOrCreate([
            'name' => 'admin1',
            'email' => 'admin1@mail.ru',
        ], [
            'email_verified_at' => now(),
            'password' => '123456',
        ]);
        $user2 = User::firstOrCreate([
            'name' => 'admin2',
            'email' => 'admin2@mail.ru',
        ], [
            'email_verified_at' => now(),
            'password' => '123456',
        ]);

        // Create tokens for easy testing.
        $user1->tokens()->create(['name' => 'Default', 'token' => hash('sha256', 12345), 'abilities' => ['*']]);
        $user2->tokens()->create(['name' => 'Default', 'token' => hash('sha256', 12345), 'abilities' => ['*']]);
    }
}
