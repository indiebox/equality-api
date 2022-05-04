<?php

namespace Tests\Feature\Broadcasting;

use App\Broadcasting\CardChannel;
use App\Broadcasting\ColumnChannel;
use App\Broadcasting\ProjectChannel;
use App\Models\Board;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChannelsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_card_channel()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $team = Team::factory()->hasAttached($user1, relationship: 'members')->create();
        $board = Board::factory()->project(Project::factory()->team($team))->create();
        $channel = new CardChannel();

        $this->assertTrue($channel->join($user1, $board));
        $this->assertFalse($channel->join($user2, $board));
    }

    public function test_column_channel()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $team = Team::factory()->hasAttached($user1, relationship: 'members')->create();
        $board = Board::factory()->project(Project::factory()->team($team))->create();
        $channel = new ColumnChannel();

        $this->assertTrue($channel->join($user1, $board));
        $this->assertFalse($channel->join($user2, $board));
    }

    public function test_project_channel()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $team = Team::factory()->hasAttached($user1, relationship: 'members')->create();
        $project = Project::factory()->team($team)->create();
        $channel = new ProjectChannel();

        $this->assertTrue($channel->join($user1, $project));
        $this->assertFalse($channel->join($user2, $project));
    }
}
