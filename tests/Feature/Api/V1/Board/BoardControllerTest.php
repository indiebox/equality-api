<?php

namespace Tests\Feature\Api\V1\Board;

use App\Http\Resources\V1\Board\BoardResource;
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

    public function test_cant_view_in_not_your_team()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/boards/' . $board->id);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/boards/' . $board->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $board->id)
            ->assertJsonStructure(['data' => ['id', 'name']]);
    }
    public function test_can_view_closed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/boards/' . $board->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $board->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'closed_at']]);
    }
    public function test_can_view_trashed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/boards/' . $board->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $board->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'deleted_at']]);
    }

    public function test_cant_update_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
        ];

        $response = $this->patchJson('/api/v1/boards/' . $board->id, $data);

        $response->assertForbidden();
    }
    public function test_cant_update_trashed()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
        ];

        $response = $this->patchJson('/api/v1/boards/' . $board->id, $data);

        $response->assertNotFound();
    }
    public function test_cant_update_closed()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
        ];

        $response = $this->patchJson('/api/v1/boards/' . $board->id, $data);

        $response->assertNotFound();
    }
    public function test_can_update()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
        ];

        $response = $this->patchJson('/api/v1/boards/' . $board->id, $data);

        $response
            ->assertOk()
            ->assertJson((new BoardResource(Board::first()))->response()->getData(true));
        $this->assertDatabaseHas('boards', ['project_id' => $project->id] + $data);
    }

    public function test_cant_close_trashed()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/close');

        $response->assertNotFound();
    }
    public function test_cant_close_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/close');

        $response->assertForbidden();
    }
    public function test_can_close()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/close');

        $board->refresh();

        $response
            ->assertOk()
            ->assertJson((new BoardResource($board))->response()->getData(true));
        $this->assertTrue($board->isClosed());
    }

    public function test_cant_open_not_closed()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/open');

        $response->assertNotFound();
    }
    public function test_cant_open_trashed()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/open');

        $response->assertNotFound();
    }
    public function test_cant_open_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/open');

        $response->assertForbidden();
    }
    public function test_cant_open_with_exceeded_boards_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        Board::factory(MaxBoardsPerProject::MAX_BOARDS)->project($project)->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/open');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.project', [
                trans('validation.max_boards_per_project', ['max' => MaxBoardsPerProject::MAX_BOARDS])
            ]);
    }
    public function test_can_open()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/open');

        $board->refresh();

        $response
            ->assertOk()
            ->assertJson((new BoardResource($board))->response()->getData(true));
        $this->assertFalse($board->isClosed());
    }

    public function test_cant_delete_closed()
    {
        $team = Team::factory();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/boards/' . $board->id);

        $response->assertNotFound();
    }
    public function test_cant_delete_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/boards/' . $board->id);

        $response->assertForbidden();
    }
    public function test_can_delete()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/boards/' . $board->id);

        $board->refresh();

        $response
            ->assertOk()
            ->assertJson((new BoardResource($board))->response()->getData(true));
        $this->assertTrue($board->trashed());
    }

    public function test_cant_restore_closed()
    {
        $team = Team::factory();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/boards/' . $board->id);

        $response->assertNotFound();
    }
    public function test_cant_restore_not_trashed()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/restore');

        $response->assertNotFound();
    }
    public function test_cant_restore_trashed_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/restore');

        $response->assertForbidden();
    }
    public function test_cant_restore_with_exceeded_boards_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        Board::factory(MaxBoardsPerProject::MAX_BOARDS)->project($project)->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/restore');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.project', [
                trans('validation.max_boards_per_project', ['max' => MaxBoardsPerProject::MAX_BOARDS])
            ]);
    }
    public function test_can_restore_trashed()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/restore');

        $board->refresh();

        $response
            ->assertOk()
            ->assertJson((new BoardResource($board))->response()->getData(true));
        $this->assertFalse($board->trashed());
    }
}
