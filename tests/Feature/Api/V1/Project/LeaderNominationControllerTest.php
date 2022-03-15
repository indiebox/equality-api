<?php

namespace Tests\Feature\Api\V1\Project;

use App\Http\Resources\V1\Team\TeamMemberResource;
use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaderNominationControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/leader-nominations');

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        User::factory(3)->hasAttached($team)->create();
        [$user1, $user2, $user3] = Team::first()->members;
        LeaderNomination::factory()
            ->project($project)
            ->voter($user1)
            ->nominated($user1)
            ->create();
        LeaderNomination::factory()
            ->project($project)
            ->voter($user2)
            ->nominated($user1)
            ->create();
        LeaderNomination::factory()
            ->project($project)
            ->voter($user3)
            ->nominated($user3)
            ->create();
        Sanctum::actingAs($user1);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/leader-nominations');

        $response
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($user1, $user2, $user3) {
                TeamMemberResource::withoutWrapping();

                $json->whereAll([
                    'data.0.nominated_id' => $user1->id,
                    'data.0.nominated' => (new TeamMemberResource($user1))->response()->getData(true),
                    'data.0.count' => 2,
                    'data.0.voters' => TeamMemberResource::collection([$user1, $user2])->response()->getData(true),
                ])->whereAll([
                    'data.1.nominated_id' => $user3->id,
                    'data.1.nominated' => (new TeamMemberResource($user3))->response()->getData(true),
                    'data.1.count' => 1,
                    'data.1.voters' => TeamMemberResource::collection([$user3])->response()->getData(true),
                ])->whereAll([
                    'data.2.nominated_id' => $user2->id,
                    'data.2.nominated' => (new TeamMemberResource($user2))->response()->getData(true),
                    'data.2.count' => 0,
                    'data.2.voters' => [],
                ])->interacted();

                TeamMemberResource::wrap('data');
            });
    }

    public function test_cant_nominate_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $user->id);

        $response->assertForbidden();
    }
    public function test_cant_nominate_not_member_of_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        $nominated = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response->assertForbidden();
    }
    public function test_can_nominate()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        $nominated = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response->assertNoContent();
        $this->assertDatabaseCount('leader_nominations', 1);

        $nominated = User::factory()->hasAttached($team)->create();

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response->assertNoContent();
        $this->assertDatabaseCount('leader_nominations', 1);

        Sanctum::actingAs($nominated);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response->assertNoContent();
        $this->assertDatabaseCount('leader_nominations', 2);
    }
    public function test_leader_recalculates_after_user_nominating()
    {
        $team = Team::factory()->create();
        $users = User::factory(3)->hasAttached($team)->create();
        $project = Project::factory()->team($team)->leader($users[0])->create();
        Sanctum::actingAs($users[0]);

        // 1(user[1]) - 0(user[0])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[1]->id);

        $response->assertNoContent();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[1]->id);

        // 1(user[2]) - 0(user[1])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[2]->id);

        $response->assertNoContent();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[2]->id);

        Sanctum::actingAs($users[1]);

        // 1(user[2]) - 1(user[1])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[1]->id);

        $response->assertNoContent();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[2]->id);

        Sanctum::actingAs($users[0]);

        // 1(user[2]) - 2(user[1])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[1]->id);

        $response->assertNoContent();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[1]->id);
    }
}
