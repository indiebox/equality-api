<?php

namespace Tests\Feature\Models;

use App\Models\Board;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BoardModelTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_team_of_deleted_project()
    {
        $team = Team::factory()->create()->fresh();
        $project = Project::factory()->team($team)->deleted()->create();
        $board = Board::factory()->project($project)->create();

        $this->assertEquals($team, $board->team);
    }
}
