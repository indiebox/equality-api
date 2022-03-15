<?php

namespace Database\Seeders;

use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class InviteSeeder extends Seeder
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
        $user2 = User::where('email', 'admin2@mail.ru')->first();
        $user3 = $teams[2]->creator();

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
