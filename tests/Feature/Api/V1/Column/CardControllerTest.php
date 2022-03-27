<?php

namespace Tests\Feature\Api\V1\Column;

use App\Http\Resources\V1\Column\ColumnCardResource;
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

    public function test_cant_view_any_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/columns/' . $column->id . '/cards');

        $response->assertForbidden();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        $cards = Card::factory(3)->column($column)->state(new Sequence(
            ['order' => 3],
            ['order' => 1],
            ['order' => 2],
        ))->create();

        $response = $this->getJson('/api/v1/columns/' . $column->id . '/cards');

        $response
            ->assertOk()
            ->assertJsonCount($cards->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonPath('data.0.id', $cards->get(1)->id)
            ->assertJsonPath('data.1.id', $cards->get(2)->id)
            ->assertJsonPath('data.2.id', $cards->get(0)->id);
    }

    public function test_cant_store_in_not_your_team()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/cards');

        $response->assertForbidden();
    }
    public function test_cant_store_with_exceeded_cards_limit()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        Card::factory(MaxCardsPerColumn::MAX_CARDS)->column($column)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/cards');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.column', [
                trans('validation.max_cards_per_column', ['max' => MaxCardsPerColumn::MAX_CARDS])
            ]);
    }
    public function test_can_store()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Card 1',
            'description' => 'Card desc',
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/cards', $data);

        $card = Card::find($response->json('data.id'));

        $response
            ->assertCreated()
            ->assertJson((new ColumnCardResource($card))->response()->getData(true));
        $this->assertDatabaseHas('cards', ['column_id' => $column->id, 'name' => $data['name']]);
        $this->assertEquals(1, $card->order);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/cards', $data);

        $card = Card::find($response->json('data.id'));

        $response
            ->assertCreated()
            ->assertJson((new ColumnCardResource($card))->response()->getData(true));
        $this->assertDatabaseHas('cards', ['column_id' => $column->id, 'name' => $data['name']]);
        $this->assertEquals(2, $card->order);
    }
    public function test_can_store_after_card()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $cards = Card::factory(2)->column($column)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
            ))->create();

        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Card 1',
            'description' => 'Card desc',
            'after_card' => $cards[0]->id,
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/cards', $data);

        $card = Card::find($response->json('data.id'));
        $cards = $cards->fresh();

        $response
            ->assertCreated()
            ->assertJson((new ColumnCardResource($card))->response()->getData(true));
        $this->assertEquals(1, $cards[0]->order);
        $this->assertEquals(2, $card->order);
        $this->assertEquals(3, $cards[1]->order);
    }
    public function test_can_store_at_first_position()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $board = Board::factory()->project($project)->create();
        $column = Column::factory()->board($board)->create();
        $cards = Card::factory(2)->column($column)
            ->state(new Sequence(
                ['order' => 1],
                ['order' => 2],
            ))->create();

        $user = User::factory()->hasAttached($team)->create();
        $data = [
            'name' => 'Card 1',
            'description' => 'Card desc',
            'after_card' => 0,
        ];
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/columns/' . $column->id . '/cards', $data);

        $card = Card::find($response->json('data.id'));
        $cards = $cards->fresh();

        $response
            ->assertCreated()
            ->assertJson((new ColumnCardResource($card))->response()->getData(true));
        $this->assertEquals(1, $card->order);
        $this->assertEquals(2, $cards[0]->order);
        $this->assertEquals(3, $cards[1]->order);
    }
}
