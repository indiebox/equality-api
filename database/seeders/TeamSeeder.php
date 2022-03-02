<?php

namespace Database\Seeders;

use App\Models\Invite;
use App\Models\Project;
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

        // Creating projects for the teams.
        Project::factory(3)->team($teams[0])->leader($user1)->create();
        Project::factory(3)->team($teams[1])->leader($user2)->create();
        Project::factory(3)->team($teams[2])->leader($user3)->create();

        // Creating additional teams for main users.
        $user1->teams()->save(Team::factory()->has(Project::factory()->leader($user1))->make());
        $user2->teams()->save(Team::factory()->has(Project::factory()->leader($user2))->make());

        // Creating common team for main users.
        Team::factory()
            ->hasAttached([$user1, $user2], [], 'members')
            ->has(Project::factory()->leader($user1))
            ->has(Project::factory()->leader($user2))
            ->create();

        // Creating invites(user1 and user2 will have 2 invites).
        Invite::factory()->team($teams[0])->inviter($user1)->invited($user2)->create();
        Invite::factory()->team($teams[0])->inviter($user1)->invited(User::factory())->declined()->create();
        Invite::factory()->team($teams[0])->inviter($user1)->invited(User::factory())->accepted()->create();

        Invite::factory()->team($teams[1])->inviter($user2)->invited($user1)->create();
        Invite::factory()->team($teams[1])->inviter($user2)->invited(User::factory())->declined()->create();
        Invite::factory()->team($teams[1])->inviter($user2)->invited(User::factory())->accepted()->create();

        Invite::factory()->team($teams[2])->inviter($user3)->invited($user1)->create();
        Invite::factory()->team($teams[2])->inviter($user3)->invited($user2)->create();
    }
}
