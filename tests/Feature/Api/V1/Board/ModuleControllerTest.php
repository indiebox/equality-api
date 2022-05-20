<?php

namespace Tests\Feature\Api\V1\Board;

use App\Models\Board;
use App\Models\Column;
use App\Models\ColumnType;
use App\Models\Module;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Rules\Api\MaxColumnsPerBoard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_enable_kanban_module_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban');

        $response->assertForbidden();
    }
    public function test_cant_set_column_from_other_board_on_enable_kanban_module()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board(Board::factory()->project($project))->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban', ['todo_column_id' => $column->id]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.todo_column_id', [
                trans('validation.exists', ['attribute' => 'todo_column_id'])
            ])
            ->assertJsonPath('errors.inprogress_column_id', [
                trans('validation.required', ['attribute' => 'inprogress column id'])
            ])
            ->assertJsonPath('errors.done_column_id', [
                trans('validation.required', ['attribute' => 'done column id'])
            ]);
    }
    public function test_cant_set_same_values_of_columns_for_kanban_module()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban', [
            'todo_column_id' => $column->id,
            'inprogress_column_id' => $column->id,
            'done_column_id' => $column->id,
            'onreview_column_id' => $column->id,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonCount(3, 'errors.todo_column_id')
            ->assertJsonCount(3, 'errors.inprogress_column_id')
            ->assertJsonCount(3, 'errors.done_column_id')
            ->assertJsonCount(3, 'errors.onreview_column_id')
            ->assertJsonPath('errors.todo_column_id', [
                trans('validation.different', ['attribute' => 'todo column id', 'other' => 'inprogress column id']),
                trans('validation.different', ['attribute' => 'todo column id', 'other' => 'done column id']),
                trans('validation.different', ['attribute' => 'todo column id', 'other' => 'onreview column id'])
            ]);
    }
    public function test_cant_create_new_column_with_exceeded_columns_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        Column::factory(MaxColumnsPerBoard::MAX_ITEMS - 2)->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $data = [
            'todo_column_id' => 0,
            'inprogress_column_id' => 0,
            'done_column_id' => 0,
        ];

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban', $data);
        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.board', [
                trans('validation.max_columns_per_board', ['max' => MaxColumnsPerBoard::MAX_ITEMS])
            ]);

        $data = [
            'todo_column_id' => 0,
            'inprogress_column_id' => Column::first()->id,
            'done_column_id' => 0,
        ];

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban', $data);

        $response->assertNoContent();
        $this->assertDatabaseCount('columns', MaxColumnsPerBoard::MAX_ITEMS);
    }
    public function test_can_enable_kanban_module()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $columns = Column::factory(2)->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $data = [
            'todo_column_id' => $columns[0]->id,
            'inprogress_column_id' => 0,
            'done_column_id' => $columns[1]->id,
        ];

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban', $data);

        $response->assertNoContent();
        $columns = Column::all();
        $this->assertEquals(ColumnType::TODO, $columns[0]->column_type_id);
        $this->assertEquals(ColumnType::IN_PROGRESS, $columns[2]->column_type_id);
        $this->assertEquals('In Progress', $columns[2]->name);
        $this->assertEquals(ColumnType::DONE, $columns[1]->column_type_id);
        $this->assertDatabaseCount('board_module', 1);

        $data = [
            'todo_column_id' => $columns[0]->id,
            'inprogress_column_id' => $columns[1]->id,
            'done_column_id' => $columns[2]->id,
        ];

        $response = $this->putJson('/api/v1/boards/' . $board->id . '/modules/kanban', $data);

        $response->assertNoContent();
        $columns = Column::all();
        $this->assertEquals(ColumnType::TODO, $columns[0]->column_type_id);
        $this->assertEquals(ColumnType::IN_PROGRESS, $columns[1]->column_type_id);
        $this->assertEquals(ColumnType::DONE, $columns[2]->column_type_id);
        $this->assertDatabaseCount('board_module', 1);
    }

    public function test_cant_disable_kanban_module_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/modules/kanban/disable');

        $response->assertForbidden();
    }
    public function test_can_disable_kanban_module()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        Module::find(Module::KANBAN)->boards()->attach($board);
        Column::factory(5)->sequence(
            ['column_type_id' => ColumnType::NONE],
            ['column_type_id' => ColumnType::TODO],
            ['column_type_id' => ColumnType::IN_PROGRESS],
            ['column_type_id' => ColumnType::DONE],
            ['column_type_id' => ColumnType::ON_REVIEW],
        )->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/boards/' . $board->id . '/modules/kanban/disable');

        $response->assertNoContent();
        $this->assertEquals(0, Column::kanbanRelated()->count());
        $this->assertDatabaseCount('board_module', 0);
    }
}
