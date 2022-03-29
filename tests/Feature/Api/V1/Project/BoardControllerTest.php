<?php

namespace Tests\Feature\Api\V1\Project;

use App\Models\Board;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Rules\Api\MaxBoardsPerProject;
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
        Board::factory()->project($project)->deleted()->create();
        Board::factory()->project($project)->closed()->create();
        $boards = Board::factory(2)->project($project)->create();

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards');

        $response
            ->assertOk()
            ->assertJsonCount($boards->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonPath('data.0.id', $boards->first()->id)
            ->assertJsonPath('data.1.id', $boards->get(1)->id);
    }

    public function test_cant_view_closed_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards/closed');

        $response->assertForbidden();
    }
    public function test_can_view_closed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        Board::factory(2)->project($project)->create();
        $closedBoards = Board::factory(2)->project($project)->closed()->create();

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards/closed');

        $response
            ->assertOk()
            ->assertJsonCount($closedBoards->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'closed_at']]])
            ->assertJsonPath('data.0.id', $closedBoards->first()->id)
            ->assertJsonPath('data.1.id', $closedBoards->get(1)->id);
    }

    public function test_cant_view_trashed_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards/trashed');

        $response->assertForbidden();
    }
    public function test_can_view_trashed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        Board::factory(2)->project($project)->create();
        $trashedBoards = Board::factory(2)->project($project)->deleted()->create();

        $response = $this->getJson('/api/v1/projects/' . $project->id . '/boards/trashed');

        $response
            ->assertOk()
            ->assertJsonCount($trashedBoards->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'deleted_at']]])
            ->assertJsonPath('data.0.id', $trashedBoards->first()->id)
            ->assertJsonPath('data.1.id', $trashedBoards->get(1)->id);
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
    public function test_cant_store_with_exceeded_boards_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        Board::factory(MaxBoardsPerProject::MAX_BOARDS)->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/boards');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.project', [
                trans('validation.max_boards_per_project', ['max' => MaxBoardsPerProject::MAX_BOARDS])
            ]);
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
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertDatabaseHas('boards', ['project_id' => $project->id, 'name' => $data['name']]);
    }
}
