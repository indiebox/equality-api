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
        $teams = Team::factory()->has(User::factory()->count(3), 'members')->count(3)->create();

        // Set creator for each team.
        foreach($teams as $team) {
            $team->members[0]->pivot->is_creator = true;
            $team->members[0]->pivot->save();
        }
    }
}
