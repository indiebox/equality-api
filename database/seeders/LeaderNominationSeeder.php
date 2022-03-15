<?php

namespace Database\Seeders;

use App\Models\LeaderNomination;
use App\Models\Project;
use Illuminate\Database\Seeder;

class LeaderNominationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $projects = Project::has('team.members')->get();

        foreach ($projects as $project) {
            $members = $project->team->members;
            $nominations = [];

            foreach ($members as $member) {
                $nominations[] = LeaderNomination::factory()
                    ->project($project)
                    ->voter($member)
                    ->nominated($members[rand(0, count($members) - 1)])
                    ->make();
            }

            $project->leaderNominations()->saveMany($nominations);
        }
    }
}
