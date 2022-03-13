<?php

namespace Tests\Feature\Api\V1\Card;

use App\Http\Resources\V1\Card\CardResource;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CardControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/cards/' . $card->id);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/cards/' . $card->id);

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
    }

    public function test_cant_update_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New card name',
        ];

        $response = $this->patchJson('/api/v1/cards/' . $card->id, $data);

        $response->assertForbidden();
    }
    public function test_can_update()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New card name',
            'description' => 'New desc',
        ];

        $response = $this->patchJson('/api/v1/cards/' . $card->id, $data);

        $response
            ->assertOk()
            ->assertJson((new CardResource(Card::first()))->response()->getData(true));
        $this->assertDatabaseHas('cards', ['column_id' => $column->id] + $data);
    }

    public function test_cant_move_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $newColumn = Column::factory()->board($board)->create();

        $card = Card::factory()->column($column)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $response->assertForbidden();
    }
    public function test_cant_move_between_teams()
    {
        $team1 = Team::factory()->create();
        $project1 = Project::factory()->team($team1)->create();
        $board1 = Board::factory()->project($project1)->create();
        $column = Column::factory()->board($board1)->create();

        $team2 = Team::factory()->create();
        $project2 = Project::factory()->team($team2)->create();
        $board2 = Board::factory()->project($project2)->create();
        $newColumn = Column::factory()->board($board2)->create();

        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team1)->hasAttached($team2)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $response->assertForbidden();
    }
    public function test_can_move()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $newColumn = Column::factory()->board($board)->create();

        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
    }
    public function test_can_move_between_projects()
    {
        $team = Team::factory()->create();
        $project1 = Project::factory()->team($team)->create();
        $project2 = Project::factory()->team($team)->create();
        $board1 = Board::factory()->project($project1)->create();
        $board2 = Board::factory()->project($project2)->create();
        $column = Column::factory()->board($board1)->create();
        $newColumn = Column::factory()->board($board2)->create();

        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
    }
    public function test_can_move_between_boards()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board1 = Board::factory()->project($project)->create();
        $board2 = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board1)->create();
        $newColumn = Column::factory()->board($board2)->create();

        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
    }

    public function test_cant_delete_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/cards/' . $card->id);

        $response->assertForbidden();
    }
    public function test_can_delete()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/cards/' . $card->id);

        $response->assertNoContent();
        $this->assertDatabaseCount('cards', 0);
    }
}
