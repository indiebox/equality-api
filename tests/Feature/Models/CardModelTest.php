<?php

namespace Tests\Feature\Models;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CardModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_team_of_deleted_project_or_board()
    {
        $team = Team::factory()->create()->fresh();
        $project = Project::factory()->team($team)->deleted()->create();
        $board = Board::factory()->project($project)->deleted()->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();

        $this->assertEquals($team, $card->team);
    }
}
