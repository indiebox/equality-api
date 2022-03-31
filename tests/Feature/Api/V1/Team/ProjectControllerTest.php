<?php

namespace Tests\Feature\Api\V1\Team;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_in_not_your_team()
    {
        $team = Team::factory()->create();
        Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/projects');

        $response->assertForbidden();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $projects = Project::factory(2)->team($team)->create();
        Project::factory()->team($team)->deleted()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/projects');

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', 2, function ($json) {
                    $json->hasAll(['id', 'name', 'image']);
                });
            })
            ->assertJsonPath('data.0.id', $projects->first()->id)
            ->assertJsonPath('data.1.id', $projects->get(1)->id);
    }
    public function test_cant_view_trashed_in_not_your_team()
    {
        $team = Team::factory()->create();
        Project::factory()->team($team)->deleted()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/projects/trashed');

        $response->assertForbidden();
    }
    public function test_can_view_trashed()
    {
        $team = Team::factory()->create();
        $projects = Project::factory(2)->team($team)->deleted()->create();
        Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/projects/trashed');

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', 2, function ($json) {
                    $json->hasAll(['id', 'name', 'image']);
                });
            })
            ->assertJsonPath('data.0.id', $projects->first()->id)
            ->assertJsonPath('data.1.id', $projects->get(1)->id);
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
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    $json->hasAll(['id', 'name', 'image']);
                });
            });
        $this->assertDatabaseHas('projects', ['team_id' => $team->id, 'leader_id' => $user->id]);
        $this->assertDatabaseHas('leader_nominations', [
            'project_id' => $project->id,
            'voter_id' => $user->id,
            'nominated_id' => $user->id,
        ]);
    }
}
