<?php

namespace Tests\Feature\Api\V1\Project;

use App\Events\Api\Projects\LeaderNominated;
use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
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
        User::factory(3)->hasAttached($team)->create();
        [$user1, $user2, $user3] = Team::first()->members;
        $project = Project::factory()->team($team)->leader($user1)->create();
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

        $response = $this->getJson("/api/v1/projects/{$project->id}/leader-nominations?include=nominated,voters,voters_count");

        $response
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($user1, $user2, $user3) {
                $json->hasAll([
                    'data.0.nominated.id',
                    'data.0.nominated.name',
                    'data.0.nominated.email',
                    'data.0.nominated.joined_at',
                ]);

                $json->whereAll([
                    'data.0.is_leader' => true,
                    'data.0.nominated.id' => $user1->id,
                    'data.0.voters_count' => 2,
                    'data.0.voters.0.id' => $user1->id,
                    'data.0.voters.1.id' => $user2->id,
                ])->whereAll([
                    'data.1.is_leader' => false,
                    'data.1.nominated.id' => $user3->id,
                    'data.1.voters_count' => 1,
                    'data.1.voters.0.id' => $user3->id,
                ])->whereAll([
                    'data.2.is_leader' => false,
                    'data.2.nominated.id' => $user2->id,
                    'data.2.voters_count' => 0,
                    'data.2.voters' => [],
                ])->interacted();
            });
    }
    public function test_can_view_with_empty_nominations()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        User::factory(3)->hasAttached($team)->create();
        [$user1, $user2, $user3] = $team->members;
        Sanctum::actingAs($user1);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/leader-nominations');

        $response
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($user1, $user2, $user3) {
                $json->whereAll([
                    'data.0.is_leader' => false,
                    'data.0.nominated.id' => $user1->id,
                    'data.0.voters' => [],
                ])->whereAll([
                    'data.1.is_leader' => false,
                    'data.1.nominated.id' => $user2->id,
                    'data.1.voters' => [],
                ])->whereAll([
                    'data.2.is_leader' => false,
                    'data.2.nominated.id' => $user3->id,
                    'data.2.voters' => [],
                ])->interacted();
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
        $team = Team::factory()
            ->hasAttached(User::factory(), [], 'members')
            ->hasAttached(User::factory(), [], 'members')
            ->create();
        $project = Project::factory()->team($team)->create();
        [$user, $nominated] = $team->members;
        Sanctum::actingAs($user);

        Event::fake();

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($user, $nominated) {
                $json->whereAll([
                    'data.0.is_leader' => true,
                    'data.0.nominated.id' => $nominated->id,
                    'data.0.voters.0.id' => $user->id,
                ])->interacted();
            });
        $this->assertDatabaseCount('leader_nominations', 1);
        Event::assertDispatched(LeaderNominated::class, function (LeaderNominated $event) use ($project) {
            return $event->project->id == $project->id;
        });

        Event::fake();

        $nominated = User::factory()->hasAttached($team)->create();
        $nominated = $team->members()->find($nominated->id);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($user, $nominated) {
                $json->whereAll([
                    'data.0.is_leader' => true,
                    'data.0.nominated.id' => $nominated->id,
                    'data.0.voters.0.id' => $user->id,
                ])->interacted();
            });
        $this->assertDatabaseCount('leader_nominations', 1);
        Event::assertDispatched(LeaderNominated::class, function (LeaderNominated $event) use ($project) {
            return $event->project->id == $project->id;
        });

        Event::fake();
        Sanctum::actingAs($nominated);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $nominated->id);

        $response
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($user, $nominated) {
                $json->whereAll([
                    'data.0.is_leader' => true,
                    'data.0.nominated.id' => $nominated->id,
                    'data.0.voters.0.id' => $user->id,
                    'data.0.voters.1.id' => $nominated->id,
                ])->interacted();
            });
        $this->assertDatabaseCount('leader_nominations', 2);
        Event::assertDispatched(LeaderNominated::class, function (LeaderNominated $event) use ($project) {
            return $event->project->id == $project->id;
        });
    }
    public function test_leader_recalculates_after_user_nominating()
    {
        $team = Team::factory()->create();
        $users = User::factory(3)->hasAttached($team)->create();
        $project = Project::factory()->team($team)->leader($users[0])->create();
        Sanctum::actingAs($users[0]);

        // 1(user[1]) - 0(user[0])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[1]->id);

        $response->assertOk();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[1]->id);

        // 1(user[2]) - 0(user[1])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[2]->id);

        $response->assertOk();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[2]->id);

        Sanctum::actingAs($users[1]);

        // 1(user[2]) - 1(user[1])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[1]->id);

        $response->assertOk();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[2]->id);

        Sanctum::actingAs($users[0]);

        // 1(user[2]) - 2(user[1])
        $response = $this->postJson('/api/v1/projects/' . $project->id . '/leader-nominations/' . $users[1]->id);

        $response->assertOk();
        $project->refresh();
        $this->assertEquals($project->leader_id, $users[1]->id);
    }
}
