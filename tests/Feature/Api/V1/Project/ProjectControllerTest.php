<?php

namespace Tests\Feature\Api\V1\Project;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_unauthorized()
    {
        $response = $this->getJson('/api/v1/projects');

        $response->assertUnauthorized();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        Project::factory()->team($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects');

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', 1, function ($json) {
                    $json->hasAll(['id', 'name', 'image']);
                })->hasAll(['links', 'meta']);
            });
    }

    public function test_cant_view_in_not_your_team()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'image']);
                });
            });
    }

    public function test_cant_view_leader_of_not_your_team()
    {
        $user = User::factory()->create();
        $project = Project::factory()->team(Team::factory())->leader($user)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/leader');

        $response->assertForbidden();
    }
    public function test_can_view_leader()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $project = Project::factory()->team($team)->leader($user)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/leader');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'email']);
                });
            });
    }

    public function test_cant_view_project_team_in_not_your_team()
    {
        $user = User::factory()->create();
        $project = Project::factory()->team(Team::factory())->leader($user)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/team');

        $response->assertForbidden();
    }
    public function test_can_view_project_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $project = Project::factory()->team($team)->leader($user)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/team');

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $team->id)
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'logo']);
                });
            });
    }

    public function test_cant_update_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
            'description' => 'New description',
        ];

        $response = $this->patchJson('/api/v1/projects/' . $project->id, $data);

        $response->assertForbidden();
    }
    public function test_can_update()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
            'description' => 'New description',
        ];

        $response = $this->patchJson('/api/v1/projects/' . $project->id, $data);

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'image']);
                });
            });
        $this->assertDatabaseHas('projects', $data);
    }

    public function test_cant_delete_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/projects/' . $project->id);

        $response->assertForbidden();
    }
    public function test_can_delete()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/projects/' . $project->id);

        $project->refresh();

        $response->assertNoContent();
        $this->assertTrue($project->trashed());
    }

    public function test_cant_restore_not_trashed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/restore');

        $response->assertNotFound();
    }
    public function test_cant_restore_trashed_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->deleted()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/restore');

        $response->assertForbidden();
    }
    public function test_can_restore_trashed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->deleted()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/restore');

        $project->refresh();

        $response->assertNoContent();
        $this->assertFalse($project->trashed());
    }
}
