<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teams = Team::all();

        $user1 = User::where('email', 'admin1@mail.ru')->first();
        Project::factory(3)->team($teams[0])->leader($user1)->create();
        Project::factory()->team($teams[3])->leader($user1)->create();

        $user2 = User::where('email', 'admin2@mail.ru')->first();
        Project::factory(3)->team($teams[1])->leader($user2)->create();
        Project::factory()->team($teams[4])->leader($user2)->create();

        $user3 = $teams[2]->creator();
        Project::factory(3)->team($teams[2])->leader($user3)->create();

        Project::factory()->team($teams[5])->leader($user1)->create();
        Project::factory()->team($teams[5])->leader($user2)->create();
    }
}
