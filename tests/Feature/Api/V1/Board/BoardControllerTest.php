<?php

namespace Tests\Feature\Api\V1\Board;

use App\Http\Resources\V1\Board\BoardResource;
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
            ->assertJson((new BoardResource($board))->response()->getData(true));
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
