<?php

namespace Tests\Feature\Api\V1\Card;

use App\Http\Resources\V1\Card\CardResource;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Rules\Api\MaxCardsPerColumn;
use Illuminate\Database\Eloquent\Factories\Sequence;
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

    public function test_cant_change_order_without_permissions()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $card = Card::factory()->column($column)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order');

        $response->assertForbidden();
    }
    public function test_cant_change_order_with_incorrect_request()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();

        Card::factory()->column($column)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ))->create();
        $card = Card::factory()->column($column)->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['after']);
    }
    public function test_cant_change_order_after_card_in_other_column()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();

        $cards = Card::factory(3)->column($column)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ))->create();
        $card = Card::factory()->column(Column::factory()->board($board))->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[0]->id]);
        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['after' => 'The selected after is invalid.']);
    }
    public function test_can_change_order_to_up()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();

        $cards = Card::factory(3)->column($column)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
                ['order' => 3],
            ))->create();
        $card = Card::factory()->column($column)->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[1]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $cards[1]->order);
        $this->assertEquals(3, $card->order);
        $this->assertEquals(4, $cards[2]->order);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => null]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $card->order);
        $this->assertEquals(2, $cards[0]->order);
        $this->assertEquals(3, $cards[1]->order);
        $this->assertEquals(4, $cards[2]->order);

        $response = $this->postJson('/api/v1/cards/' . $cards[1]->id . '/order', ['after' => null]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($cards[1]))->response()->getData(true));
        $this->assertEquals(1, $cards[1]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[0]->order);
        $this->assertEquals(4, $cards[2]->order);

        $response = $this->postJson('/api/v1/cards/' . $cards[2]->id . '/order', ['after' => $card->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($cards[2]))->response()->getData(true));
        $this->assertEquals(1, $cards[1]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[2]->order);
        $this->assertEquals(4, $cards[0]->order);
    }
    public function test_can_change_order_to_bottom()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();

        $card = Card::factory()->column($column)->order(1)->create();
        $cards = Card::factory(3)->column($column)
            ->state(new Sequence(
                ['order' => 2],
                ['order' => 3],
                ['order' => 4],
            ))->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[1]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $cards[1]->order);
        $this->assertEquals(3, $card->order);
        $this->assertEquals(4, $cards[2]->order);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[2]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $cards[1]->order);
        $this->assertEquals(3, $cards[2]->order);
        $this->assertEquals(4, $card->order);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[0]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[1]->order);
        $this->assertEquals(4, $cards[2]->order);

        $response = $this->postJson('/api/v1/cards/' . $cards[0]->id . '/order', ['after' => $cards[2]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($cards[0]))->response()->getData(true));
        $this->assertEquals(1, $card->order);
        $this->assertEquals(2, $cards[1]->order);
        $this->assertEquals(3, $cards[2]->order);
        $this->assertEquals(4, $cards[0]->order);
    }
    public function test_order_not_recalculated_with_inconsistent_order_on_ordering_to_up()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();

        $cards = Card::factory(2)->column($column)
        ->state(new Sequence(
            ['order' => 1],
            ['order' => 3],
        ))->create();
        $card = Card::factory()->column($column)->order(4)->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[0]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[1]->order);
    }
    public function test_order_not_recalculated_with_inconsistent_order_on_ordering_to_bottom()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();

        $card = Card::factory()->column($column)->order(1)->create();
        $cards = Card::factory(2)->column($column)
        ->state(new Sequence(
            ['order' => 2],
            ['order' => 4],
        ))->create();

        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/order', ['after' => $cards[0]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[1]->order);
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
    public function test_cant_move_in_column_with_exceed_cards_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $newColumn = Column::factory()->board($board)->create();
        Card::factory(MaxCardsPerColumn::MAX_CARDS)->column($newColumn)->create();

        $card = Card::factory()->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.column', [
                trans('validation.max_cards_per_column', ['max' => MaxCardsPerColumn::MAX_CARDS])
            ]);
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

        $card->refresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
        $this->assertEquals(1, $card->order);

        $card = Card::factory()->column($column)->create();

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id);

        $card->refresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
        $this->assertEquals(2, $card->order);
    }
    public function test_can_move_after_card()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $newColumn = Column::factory()->board($board)->create();
        $cards = Card::factory(2)->column($newColumn)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
            ))->create();

        $card = Card::factory()->column($column)->order(1)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id, ['after_card' => $cards[0]->id]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[1]->order);
    }
    public function test_can_move_at_first_position()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $newColumn = Column::factory()->board($board)->create();
        $cards = Card::factory(2)->column($newColumn)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
            ))->create();

        $card = Card::factory()->column($column)->order(1)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/cards/' . $card->id . '/move/' . $newColumn->id, ['after_card' => null]);

        $card->refresh();
        $cards = $cards->fresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
        $this->assertEquals(1, $card->order);
        $this->assertEquals(2, $cards[0]->order);
        $this->assertEquals(3, $cards[1]->order);
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

        $card->refresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
        $this->assertEquals(1, $card->order);
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

        $card->refresh();

        $response
            ->assertOk()
            ->assertJson((new CardResource($card))->response()->getData(true));
        $this->assertDatabaseMissing('cards', ['column_id' => $column->id]);
        $this->assertDatabaseHas('cards', ['column_id' => $newColumn->id]);
        $this->assertEquals(1, $card->order);
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
