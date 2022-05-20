<?php

namespace Tests\Feature\Services\Modules;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\ColumnType;
use App\Models\Module;
use App\Models\Project;
use App\Models\Team;
use App\Services\Contracts\Modules\KanbanService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KanbanServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var ModuleService
     */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(KanbanService::class);
    }

    public function test_enable_kanban_method_with_single_column()
    {
        $board = Board::factory()->project(Project::factory()->team(Team::factory()))->create();
        $columns = Column::factory()->board($board)->create();

        $this->assertDatabaseCount('board_module', 0);

        $this->service->enableKanban($board, [
            'todo_column_id' => $columns,
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(1, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::TODO, $columns[0]->column_type_id);

        $this->service->enableKanban($board, [
            'todo_column_id' => 0,
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(1, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::NONE, $columns[0]->column_type_id);
        $this->assertEquals(ColumnType::TODO, $columns[1]->column_type_id);

        $this->service->enableKanban($board, [
            'todo_column_id' => $columns[0],
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(1, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::TODO, $columns[0]->column_type_id);
        $this->assertEquals(ColumnType::NONE, $columns[1]->column_type_id);
    }
    public function test_enable_kanban_method_without_optional_columns()
    {
        $board = Board::factory()->project(Project::factory()->team(Team::factory()))->create();
        $columns = Column::factory(4)->board($board)->create();

        $this->assertDatabaseCount('board_module', 0);

        $this->service->enableKanban($board, [
            'todo_column_id' => $columns[0],
            'inprogress_column_id' => $columns[1],
            'done_column_id' => $columns[2],
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(3, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::NONE, $columns[3]->column_type_id);

        $this->service->enableKanban($board, [
            'todo_column_id' => $columns[0],
            'inprogress_column_id' => 0,
            'done_column_id' => $columns[2],
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(3, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::IN_PROGRESS, $columns[4]->column_type_id);
        $this->assertEquals('In Progress', $columns[4]->name);
    }
    public function test_enable_kanban_method_with_optional_columns()
    {
        $board = Board::factory()->project(Project::factory()->team(Team::factory()))->create();
        $columns = Column::factory(5)->board($board)->create();

        $this->assertDatabaseCount('board_module', 0);

        $this->service->enableKanban($board, [
            'todo_column_id' => $columns[0],
            'inprogress_column_id' => $columns[1],
            'done_column_id' => $columns[2],
            'onreview_column_id' => $columns[3],
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(4, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::NONE, $columns[4]->column_type_id);

        $this->service->enableKanban($board, [
            'todo_column_id' => $columns[0],
            'inprogress_column_id' => 0,
            'done_column_id' => $columns[2],
            'onreview_column_id' => 0,
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(4, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::IN_PROGRESS, $columns[5]->column_type_id);
        $this->assertEquals('In Progress', $columns[5]->name);
        $this->assertEquals(ColumnType::ON_REVIEW, $columns[6]->column_type_id);
        $this->assertEquals('On Review', $columns[6]->name);

        // Dont pass optional columns, so columns with that types should unset.
        $this->service->enableKanban($board, [
            'todo_column_id' => $columns[0],
            'inprogress_column_id' => $columns[5],
            'done_column_id' => $columns[2],
        ]);

        $this->assertDatabaseCount('board_module', 1);
        $columns = Column::all();
        $this->assertEquals(3, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
        $this->assertEquals(ColumnType::IN_PROGRESS, $columns[5]->column_type_id);
        $this->assertEquals('In Progress', $columns[5]->name);
        $this->assertEquals(ColumnType::NONE, $columns[6]->column_type_id);
        $this->assertEquals('On Review', $columns[6]->name);
    }

    public function test_disable_kanban_method()
    {
        $board = Board::factory()->project(Project::factory()->team(Team::factory()))->create();
        Module::find(Module::KANBAN)->boards()->attach($board);
        $columns = Column::factory(5)->board($board)->sequence(
            ['column_type_id' => ColumnType::NONE],
            ['column_type_id' => ColumnType::TODO],
            ['column_type_id' => ColumnType::IN_PROGRESS],
            ['column_type_id' => ColumnType::DONE],
            ['column_type_id' => ColumnType::ON_REVIEW],
        )->create();

        $this->assertDatabaseCount('board_module', 1);

        $this->service->disableKanban($board);

        $this->assertDatabaseCount('board_module', 0);
        $columns = $columns->fresh();
        $this->assertEquals(0, $columns->filter(fn($column) => $column->column_type_id != ColumnType::NONE)->count());
    }

    public function test_can_move_card_to_column_module()
    {
        $board = Board::factory()->project(Project::factory()->team(Team::factory()))->create();
        $columns = Column::factory(4)->board($board)->sequence(
            ['column_type_id' => ColumnType::NONE],
            ['column_type_id' => ColumnType::TODO],
            ['column_type_id' => ColumnType::IN_PROGRESS],
            ['column_type_id' => ColumnType::DONE],
        )->create();

        // None -> ToDo
        $card = Card::factory()->column($columns[0])->create();
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[3]));

        // ToDo -> None; ToDo -> InProgress
        $card = Card::factory()->column($columns[1])->create();
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[3]));

        // InProgress -> ToDo; InProgress -> Done
        $card = Card::factory()->column($columns[2])->create();
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[3]));

        // Done -> InProgress
        $card = Card::factory()->column($columns[3])->create();
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[3]));
    }
    public function test_can_move_card_to_column_module_with_on_review_column()
    {
        $board = Board::factory()->project(Project::factory()->team(Team::factory()))->create();
        $columns = Column::factory(5)->board($board)->sequence(
            ['column_type_id' => ColumnType::NONE],
            ['column_type_id' => ColumnType::TODO],
            ['column_type_id' => ColumnType::IN_PROGRESS],
            ['column_type_id' => ColumnType::ON_REVIEW],
            ['column_type_id' => ColumnType::DONE],
        )->create();

        // None -> ToDo
        $card = Card::factory()->column($columns[0])->create();
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[3]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[4]));

        // ToDo -> None; ToDo -> InProgress
        $card = Card::factory()->column($columns[1])->create();
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[3]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[4]));

        // InProgress -> ToDo; InProgress -> OnReview
        $card = Card::factory()->column($columns[2])->create();
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[3]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[4]));

        // OnReview -> InProgress; OnReview -> Done
        $card = Card::factory()->column($columns[3])->create();
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[3]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[4]));

        // Done -> OnReview
        $card = Card::factory()->column($columns[4])->create();
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[0]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[1]));
        $this->assertFalse($this->service->canMoveCardToColumn($card, $columns[2]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[3]));
        $this->assertTrue($this->service->canMoveCardToColumn($card, $columns[4]));
    }
}
