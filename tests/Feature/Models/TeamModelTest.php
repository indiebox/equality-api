<?php

namespace Tests\Feature\Models;

use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TeamModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_projects_leader_nominations_for_deleted_projects()
    {
        $team = Team::factory()->create();
        Project::factory(2)->team($team)->create();
        Project::factory()->team($team)->deleted()->create();
        $projects = Project::withTrashed()->get();
        $nomination1 = LeaderNomination::factory()->project($projects[0])
            ->voter(User::factory())
            ->nominated(User::factory())
            ->create();
        $nomination2 = LeaderNomination::factory()->project($projects[1])
            ->voter(User::factory())
            ->nominated(User::factory())
            ->create();
        $nomination3 = LeaderNomination::factory()->project($projects[2])
            ->voter(User::factory())
            ->nominated(User::factory())
            ->create();

        $this->assertCount(3, $team->projectsLeaderNominations);
        $this->assertEquals($nomination1->id, $team->projectsLeaderNominations[0]->id);
        $this->assertEquals($nomination2->id, $team->projectsLeaderNominations[1]->id);
        $this->assertEquals($nomination3->id, $team->projectsLeaderNominations[2]->id);
    }
}
