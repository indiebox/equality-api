<?php

namespace Tests\Feature\Services\Project;

use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\Contracts\Projects\LeaderService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LeaderServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var LeaderService
     */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LeaderService::class);
    }

    public function test_delete_user_nominations_method()
    {
        $team = Team::factory()->create();
        $users = User::factory(3)->hasAttached($team)->create();
        $project = Project::factory()->team($team)->leader($users[0])->create();
        LeaderNomination::factory()->project($project)->voter($users[0])->nominated($users[0])->create();
        LeaderNomination::factory()->project($project)->voter($users[1])->nominated($users[0])->create();
        LeaderNomination::factory()->project($project)->voter($users[2])->nominated($users[1])->create();

        $this->service->deleteUserNominations($users[0], $team);

        $this->assertDatabaseCount('leader_nominations', 1);
        $this->assertDatabaseHas('leader_nominations', ['voter_id' => $users[2]->id, 'nominated_id' => $users[1]->id]);
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[1]->id);
    }

    public function test_determine_new_leader_method()
    {
        $users = User::factory(3)->create();
        $team = Team::factory()->hasAttached($users, [], 'members')->create();
        $projectCreator = User::factory()->hasAttached($team)->create();
        // Project creator: 0 - the initial leader
        // Other user: 0
        $project = Project::factory()->team($team)->leader($projectCreator)->create();

        // 1 - 0
        LeaderNomination::factory()->project($project)->voter($users[0])->nominated($projectCreator)->create();
        $this->service->determineNewLeader($project);
        $project->refresh();

        $this->assertEquals($project->leader_id, $projectCreator->id);

        // 1 - 1, Project creator stays the leader.
        LeaderNomination::factory()->project($project)->voter($users[1])->nominated($users[1])->create();
        $this->service->determineNewLeader($project);
        $project->refresh();

        $this->assertEquals($project->leader_id, $projectCreator->id);

        // 1 - 2, Other user becomes the leader.
        LeaderNomination::factory()->project($project)->voter($users[2])->nominated($users[1])->create();
        $this->service->determineNewLeader($project);
        $project->refresh();

        $this->assertEquals($project->leader_id, $users[1]->id);

        // 2 - 2, Other user stays the leader.
        LeaderNomination::factory()->project($project)->voter($projectCreator)->nominated($projectCreator)->create();
        $this->service->determineNewLeader($project);
        $project->refresh();

        $this->assertEquals($project->leader_id, $users[1]->id);
    }
    public function test_recalculates_project_leader_by_most_old_member()
    {
        $team = Team::factory()->create();
        $oldMember = User::factory()->hasAttached($team)->create();
        $users = User::factory(3)->hasAttached($team)->create();
        $project = Project::factory()->team($team)->leader($users[0])->create();

        $this->service->determineNewLeader($project);
        $project->refresh();

        $this->assertEquals($project->leader_id, $oldMember->id);
    }

    public function test_make_nominations_collection_method_without_nominations()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        User::factory(3)->hasAttached($team)->create();
        $users = $project->team->members;
        $project = Project::factory()->team($team)->leader($users[0])->create();

        $collection = $this->service->makeNominationsCollection($project);

        $this->assertEquals([
            [
                'nominated_id' => $users[0]->id,
                'nominated' => $users[0],
                'voters_count' => 0,
                'voters' => [],
                'is_leader' => true,
            ],
            [
                'nominated_id' => $users[1]->id,
                'nominated' => $users[1],
                'voters_count' => 0,
                'voters' => [],
                'is_leader' => false,
            ],
            [
                'nominated_id' => $users[2]->id,
                'nominated' => $users[2],
                'voters_count' => 0,
                'voters' => [],
                'is_leader' => false,
            ],
        ], $collection->toArray());
    }
    public function test_make_nominations_collection_method_with_nominations()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        User::factory(3)->hasAttached($team)->create();
        $users = $project->team->members;
        $project = Project::factory()->team($team)->leader($users[0])->create();
        LeaderNomination::factory()->project($project)->voter($users[0])->nominated($users[0])->create();
        LeaderNomination::factory()->project($project)->voter($users[1])->nominated($users[0])->create();
        LeaderNomination::factory()->project($project)->voter($users[2])->nominated($users[2])->create();

        $collection = $this->service->makeNominationsCollection($project);

        $this->assertEquals([
            [
                'nominated_id' => $users[0]->id,
                'nominated' => $users[0],
                'voters_count' => 2,
                'voters' => new EloquentCollection([$users[0], $users[1]]),
                'is_leader' => true,
            ],
            [
                'nominated_id' => $users[2]->id,
                'nominated' => $users[2],
                'voters_count' => 1,
                'voters' => new EloquentCollection([2 => $users[2]]),
                'is_leader' => false,
            ],
            [
                'nominated_id' => $users[1]->id,
                'nominated' => $users[1],
                'voters_count' => 0,
                'voters' => [],
                'is_leader' => false,
            ],
        ], $collection->toArray());
    }
}
