<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(TeamSeeder::class);
        $this->call(BoardSeeder::class);
        $this->call(ColumnSeeder::class);
        $this->call(CardSeeder::class);
    }
}
