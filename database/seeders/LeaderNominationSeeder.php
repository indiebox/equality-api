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

            $countsForLeader = ceil($members->count() / 2);

            foreach ($members as $member) {
                if ($countsForLeader != 0) {
                    $nominated = $members->where('id', $project->leader_id)->first();
                    $countsForLeader--;
                } else {
                    $nominated = $members[rand(0, count($members) - 1)];
                }

                $nominations[] = LeaderNomination::factory()
                    ->project($project)
                    ->voter($member)
                    ->nominated($nominated)
                    ->make();
            }

            $project->leaderNominations()->saveMany($nominations);
        }
    }
}
