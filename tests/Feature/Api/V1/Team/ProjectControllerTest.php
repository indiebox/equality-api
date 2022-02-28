<?php

namespace Tests\Feature\Api\V1\Team;

use App\Http\Resources\V1\Team\TeamProjectResource;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_in_not_your_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Project::factory()->team(Team::factory())->create();

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/projects');

        $response->assertForbidden();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        Project::factory()->team($team)->create();
        $projects = Project::all();

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/projects');

        $response
            ->assertOk()
            ->assertJson(TeamProjectResource::collection($projects)->response()->getData(true));
    }

    public function test_cant_store_in_not_your_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/projects');

        $response->assertForbidden();
    }
    public function test_can_store()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Test project',
            'description' => 'Desc',
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/projects', $data);

        $project = Project::find($response->json('data.id'));

        $response
            ->assertCreated()
            ->assertJson((new TeamProjectResource($project))->response()->getData(true));
        $this->assertDatabaseHas('projects', ['team_id' => $team->id, 'leader_id' => $user->id]);
        $this->assertDatabaseHas('leader_nominations', [
            'project_id' => $project->id,
            'voter_id' => $user->id,
            'nominated_id' => $user->id,
        ]);
    }
}
