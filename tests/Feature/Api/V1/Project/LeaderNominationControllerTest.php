<?php

namespace Tests\Feature\Api\V1\Project;

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
        $user1 = User::factory()->hasAttached($team)->create();
        $user2 = User::factory()->hasAttached($team)->create();
        $user3 = User::factory()->hasAttached($team)->create();
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
                $json->whereAll([
                    'data.0.nominated' => $user1,
                    'data.0.count' => 2,
                    'data.0.voters' => [$user1->id, $user2->id],
                ])->whereAll([
                    'data.1.nominated' => $user3,
                    'data.1.count' => 1,
                    'data.1.voters' => [$user3->id],
                ])->interacted();
            });
    }

    public function test_cant_nominate_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/1');

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
}
