<?php

namespace Tests\Feature\Api\V1\Team;

use App\Events\Api\UserLeaveTeam;
use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_unauthorized()
    {
        $response = $this->getJson('/api/v1/teams');

        $response->assertUnauthorized();
    }
    public function test_can_view_any()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams');

        $response->assertOk();
    }

    public function test_cant_view_without_permissions()
    {
        $user = User::factory()->create();
        $teamId = Team::factory()->create()->id;
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $teamId);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $teamId = Team::factory()->has(User::factory(), 'members')->create()->id;
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $teamId);

        $response->assertOk();
    }

    public function test_cant_view_members_without_permissions()
    {
        $user = User::factory()->create();
        $teamId = Team::factory()->create()->id;
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $teamId . '/members');

        $response->assertForbidden();
    }
    public function test_can_view_members() {
        $teamId = Team::factory()->has(User::factory(), 'members')->create()->id;
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $teamId . '/members');

        $response->assertOk();
    }

    public function test_can_store()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'Team name',
            'description' => 'Test',
        ];

        $response = $this->postJson('/api/v1/teams', $data);

        $response->assertCreated();
        $this->assertDatabaseCount('teams', 1);
        $this->assertDatabaseHas('teams', $data);
        $this->assertDatabaseCount('team_user', 1);
        $this->assertDatabaseHas('team_user', ['user_id' => $user->id, 'is_creator' => true]);
    }

    public function test_cant_update_without_permissions()
    {
        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
            'description' => 'New description',
        ];

        $response = $this->patchJson('/api/v1/teams/' . $team->id, $data);

        $response->assertForbidden();
    }
    public function test_can_update()
    {
        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
            'description' => 'New description',
        ];

        $response = $this->patchJson('/api/v1/teams/' . $team->id, $data);

        $response->assertOk();
        $this->assertDatabaseHas('teams', array_merge($data, ['url' => $team->url]));
    }

    public function test_cant_leave()
    {
        $user = User::factory()->create();
        $team = Team::factory()->has(User::factory(), 'members')->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertForbidden();
    }
    public function test_can_leave()
    {
        Event::fake();
        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        Event::assertDispatched(UserLeaveTeam::class);
    }

    public function test_team_deleted_after_last_member_leave()
    {
        $team = Team::factory()->has(User::factory()->count(2), 'members')->create();
        $user = User::all()->first();
        Sanctum::actingAs($user);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();

        $user = User::all()->last();
        Sanctum::actingAs($user);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        $this->assertDatabaseCount('teams', 0);
    }
    public function test_associated_leader_nominations_deleted_after_user_leave_team()
    {
        $leavedTeam = Team::factory()->create();
        $team = Team::factory()->create();
        $user1 = User::factory()->hasAttached($leavedTeam)->hasAttached($team)->create();
        $user2 = User::factory()->hasAttached($leavedTeam)->create();
        $user3 = User::factory()->hasAttached($leavedTeam)->create();
        $project1 = Project::factory()->team($leavedTeam)->create();
        $project2 = Project::factory()->team($leavedTeam)->create();
        $project3 = Project::factory()->team($team)->create();
        LeaderNomination::factory()->project($project1)->voter($user1)->nominated($user2)->create();
        LeaderNomination::factory()->project($project1)->voter($user2)->nominated($user1)->create();
        LeaderNomination::factory()->project($project2)->voter($user3)->nominated($user3)->create();
        LeaderNomination::factory()->project($project3)->voter($user1)->nominated($user1)->create();
        Sanctum::actingAs($user1);

        $response = $this->postJson('/api/v1/teams/' . $leavedTeam->id . '/leave');

        $response->assertNoContent();
        $this->assertDatabaseCount('leader_nominations', 2);
        $this->assertDatabaseHas('leader_nominations', [
            'project_id' => $project2->id,
            'voter_id' => $user3->id,
            'nominated_id' => $user3->id,
        ]);
        $this->assertDatabaseHas('leader_nominations', [
            'project_id' => $project3->id,
            'voter_id' => $user1->id,
            'nominated_id' => $user1->id,
        ]);
    }
    public function test_project_leader_cleared_after_leader_leave_team()
    {
        $leavedTeam = Team::factory()->hasAttached(User::factory(2), [], 'members')->create();
        $team = Team::factory()->hasAttached(User::factory(2), [], 'members')->create();
        $user1 = User::factory()->hasAttached($leavedTeam)->hasAttached($team)->create();
        $project1 = Project::factory()->team($leavedTeam)->leader($user1)->create();
        $project2 = Project::factory()->team($team)->leader($user1)->create();
        Sanctum::actingAs($user1);

        $response = $this->postJson('/api/v1/teams/' . $leavedTeam->id . '/leave');

        $response->assertNoContent();
        $project1->refresh();
        $project2->refresh();
        $this->assertNull($project1->leader_id);
        $this->assertEquals($project2->leader_id, $user1->id);
    }
    public function test_logo_deleted_after_team_deleting()
    {
        Storage::fake();

        $team = Team::factory()->has(User::factory()->count(2), 'members')->create();
        $user = User::all()->first();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/teams/' . $team->id . '/logo', ['logo' => UploadedFile::fake()->image('test.jpg')]);
        $team->refresh();
        Storage::assertExists($team->logo);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        Storage::assertExists($team->logo);

        $user = User::all()->last();
        Sanctum::actingAs($user);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        $this->assertDatabaseCount('teams', 0);
        Storage::assertMissing($team->logo);
    }
}
