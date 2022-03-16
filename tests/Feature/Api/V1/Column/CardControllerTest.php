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
        $cards = Card::factory(2)->column($column)->create();

        $response = $this->getJson('/api/v1/columns/' . $column->id . '/cards');

        $response
            ->assertOk()
            ->assertJson(ColumnCardResource::collection($cards)->response()->getData(true));
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
    }
}