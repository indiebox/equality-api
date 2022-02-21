<?php

namespace Database\Seeders;

use App\Models\Invite;
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
        $teams = Team::factory(3)->has(User::factory(3), 'members')->create();

        // Set the team creators.
        $teams[0]->members()->attach($user1 = User::where('email', 'admin1@mail.ru')->first(), ['is_creator' => true]);
        $teams[1]->members()->attach($user2 = User::where('email', 'admin2@mail.ru')->first(), ['is_creator' => true]);
        $teams[2]->members()->attach($user3 = User::factory()->create(), ['is_creator' => true]);

        // Creating additional teams for main users.
        $user1->teams()->saveMany(Team::factory()->make());
        $user2->teams()->saveMany(Team::factory()->make());

        // Creating common team for main users.
        Team::factory()->hasAttached([$user1, $user2])->create();

        // Creating invites(user1 and user2 will have 2 invites).
        Invite::factory()->team($teams[0])->inviter($user1)->invited($user2)->create();
        Invite::factory()->team($teams[1])->inviter($user2)->invited($user1)->create();
        Invite::factory()->team($teams[2])->inviter($user3)->invited($user1)->create();
        Invite::factory()->team($teams[2])->inviter($user3)->invited($user2)->create();
    }
}
