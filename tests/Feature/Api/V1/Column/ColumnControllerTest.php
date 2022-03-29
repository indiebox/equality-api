<?php

namespace Tests\Feature\Api\V1\Column;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ColumnControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_in_not_your_team()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/columns/' . $column->id);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/columns/' . $column->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $column->id)
            ->assertJsonStructure(['data' => ['id', 'name']]);
    }

    public function test_cant_update_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New column',
        ];

        $response = $this->patchJson('/api/v1/columns/' . $column->id, $data);

        $response->assertForbidden();
    }
    public function test_can_update()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
        ];

        $response = $this->patchJson('/api/v1/columns/' . $column->id, $data);

        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertDatabaseHas('columns', ['board_id' => $board->id] + $data);
    }

    public function test_cant_change_order_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/order');

        $response->assertForbidden();
    }
    public function test_cant_change_order_with_incorrect_request()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        Column::factory()->board($board)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ))->create();

        $column = Column::factory()->board($board)->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/order');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['after']);
    }
    public function test_cant_change_order_after_card_in_other_column()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $columns = Column::factory(3)->board($board)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ))->create();

        $column = Column::factory()->board(Board::factory()->project($project))->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/order', ['after' => $columns[0]->id]);
        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['after' => 'The selected after is invalid.']);
    }
    public function test_can_change_order()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $columns = Column::factory(3)->board($board)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ))->create();
        $column = Column::factory()->board($board)->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        // Move to up dirrection
        $response = $this->postJson('/api/v1/columns/' . $column->id . '/order', ['after' => $columns[1]->id]);

        $column->refresh();
        $columns = $columns->fresh();

        $response->assertNoContent();
        $this->assertEquals(1, $columns[0]->order);
        $this->assertEquals(2, $columns[1]->order);
        $this->assertEquals(3, $column->order);
        $this->assertEquals(4, $columns[2]->order);

        // First position
        $response = $this->postJson('/api/v1/columns/' . $column->id . '/order', ['after' => 0]);

        $column->refresh();
        $columns = $columns->fresh();

        $response->assertNoContent();
        $this->assertEquals(1, $column->order);
        $this->assertEquals(2, $columns[0]->order);
        $this->assertEquals(3, $columns[1]->order);
        $this->assertEquals(4, $columns[2]->order);

        // Move to bottom direction
        $response = $this->postJson('/api/v1/columns/' . $column->id . '/order', ['after' => $columns[2]->id]);

        $column->refresh();
        $columns = $columns->fresh();

        $response->assertNoContent();
        $this->assertEquals(1, $columns[0]->order);
        $this->assertEquals(2, $columns[1]->order);
        $this->assertEquals(3, $columns[2]->order);
        $this->assertEquals(4, $column->order);
    }

    public function test_cant_delete_without_permissions()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/columns/' . $column->id);

        $response->assertForbidden();
    }
    public function test_can_delete()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/columns/' . $column->id);

        $response->assertNoContent();
        $this->assertDatabaseCount('columns', 0);
    }
}
