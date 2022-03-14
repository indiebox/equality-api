<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = Team::factory(3)
            ->has(User::factory(3), 'members')
            ->create();

        // Set the team creators.
        $teams[0]->members()->attach($user1 = User::where('email', 'admin1@mail.ru')->first(), ['is_creator' => true]);
        $teams[1]->members()->attach($user2 = User::where('email', 'admin2@mail.ru')->first(), ['is_creator' => true]);
        $teams[2]->members()->attach($user3 = User::factory()->create(), ['is_creator' => true]);

        // Creating additional teams for main users.
        $user1->teams()->save(Team::factory()->make());
        $user2->teams()->save(Team::factory()->make());

        // Creating common team for main users.
        Team::factory()
            ->hasAttached([$user1, $user2], [], 'members')
            ->create();
    }
}
