<?php

namespace Tests\Feature\Console\Pruning;

use App\Models\Board;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeleteOldArchivedBoardsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_delete_not_trashed_boards()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        Board::factory(2)->project($project)->create();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('boards', 2);
    }

    public function test_cant_delete_trashed_boards_before_time()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        Board::factory(2)->project($project)->deleted()->create();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('boards', 2);
    }

    public function test_can_delete_trashed_boards_after_time()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board1 = Board::factory()->project($project)->create();
        $board2 = Board::factory()->project($project)->create(['deleted_at' => now()->addWeek()]);
        Board::factory()->project($project)->deleted()->create();

        $this->travel(1)->weeks();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('boards', 2);
        $this->assertDatabaseHas('boards', ['id' => $board1->id]);
        $this->assertDatabaseHas('boards', ['id' => $board2->id]);
    }
}
