<?php

namespace Tests\Feature\Api\V1\Project;

use App\Http\Resources\V1\Project\ProjectBoardResource;
use App\Models\Board;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BoardControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards');

        $response->assertForbidden();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $boards = Board::factory(2)->project($project)->create();

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards');

        $response
            ->assertOk()
            ->assertJson(ProjectBoardResource::collection($boards)->response()->getData(true));
    }

    public function test_cant_store_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/boards');

        $response->assertForbidden();
    }
    public function test_can_store()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Test project',
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/boards', $data);

        $board = Board::find($response->json('data.id'));

        $response
            ->assertCreated()
            ->assertJson((new ProjectBoardResource($board))->response()->getData(true));
        $this->assertDatabaseHas('boards', ['project_id' => $project->id, 'name' => $data['name']]);
    }
}
