<?php

namespace Tests\Feature\Api\V1\Board;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Rules\Api\MaxColumnsPerBoard;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ColumnControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/boards/' . $board->id . '/columns');

        $response->assertForbidden();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $columns = Column::factory(2)->board($board)->create();

        $response = $this->getJson('/api/v1/boards/' . $board->id . '/columns');

        $response
            ->assertOk()
            ->assertJsonCount($columns->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonPath('data.0.id', $columns->first()->id)
            ->assertJsonPath('data.1.id', $columns->get(1)->id);
    }
    public function test_can_view_any_in_closed_board()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->closed()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $columns = Column::factory(2)->board($board)->create();

        $response = $this->getJson('/api/v1/boards/' . $board->id . '/columns');

        $response
            ->assertOk()
            ->assertJsonCount($columns->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonPath('data.0.id', $columns->first()->id)
            ->assertJsonPath('data.1.id', $columns->get(1)->id);
    }
    public function test_can_view_any_in_trashed_board()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $columns = Column::factory(2)->board($board)->create();

        $response = $this->getJson('/api/v1/boards/' . $board->id . '/columns');

        $response
            ->assertOk()
            ->assertJsonCount($columns->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonPath('data.0.id', $columns->first()->id)
            ->assertJsonPath('data.1.id', $columns->get(1)->id);
    }

    public function test_cant_store_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/columns');

        $response->assertForbidden();
    }
    public function test_cant_store_with_exceeded_columns_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        Column::factory(MaxColumnsPerBoard::MAX_COLUMNS)->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Col 1',
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/columns', $data);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.board', [
                trans('validation.max_columns_per_board', ['max' => MaxColumnsPerBoard::MAX_COLUMNS])
            ]);
    }
    public function test_can_store()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Col 1',
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/columns', $data);

        $column = Column::find($response->json('data.id'));

        $response
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertDatabaseHas('columns', ['board_id' => $board->id, 'name' => $data['name']]);
        $this->assertEquals(1, $column->order);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/columns', $data);

        $column = Column::find($response->json('data.id'));

        $response
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertDatabaseHas('columns', ['board_id' => $board->id, 'name' => $data['name']]);
        $this->assertEquals(2, $column->order);
    }
    public function test_can_store_after_card()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $columns = Column::factory(2)->board($board)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
            ))->create();

        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Col 1',
            'after_column' => $columns[0]->id,
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/columns', $data);

        $column = Column::find($response->json('data.id'));
        $columns = $columns->fresh();

        $response
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertEquals(1, $columns[0]->order);
        $this->assertEquals(2, $column->order);
        $this->assertEquals(3, $columns[1]->order);
    }
    public function test_can_store_at_first_position()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $columns = Column::factory(2)->board($board)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
            ))->create();

        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Col 1',
            'after_column' => 0,
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/columns', $data);

        $column = Column::find($response->json('data.id'));
        $columns = $columns->fresh();

        $response
            ->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertEquals(1, $column->order);
        $this->assertEquals(2, $columns[0]->order);
        $this->assertEquals(3, $columns[1]->order);
    }
}
