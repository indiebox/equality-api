<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InviteControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_unauthorized()
    {
        $response = $this->getJson('/api/v1/invites');

        $response->assertUnauthorized();
    }
    public function test_cant_view_any_not_pending()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Invite::factory()->accepted()->team(Team::factory())->invited($user)->create();
        Invite::factory()->declined()->team(Team::factory())->invited($user)->create();

        $response = $this->getJson('/api/v1/invites');

        $response
            ->assertOk()
            ->assertJsonPath('data', []);
    }
    public function test_can_view_any()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $invites = Invite::factory(2)->team(Team::factory())->invited($user)->create();

        $response = $this->getJson('/api/v1/invites');

        $response
            ->assertOk()
            ->assertJsonCount($invites->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'team', 'inviter']]]);
    }

    public function test_cant_accept_not_pending_invite()
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->accepted()->invited($user)->team(Team::factory())->create();
        $invite2 = Invite::factory()->declined()->invited($user)->team(Team::factory())->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/invites/' . $invite->id . '/accept');

        $response->assertNotFound();

        $response = $this->postJson('/api/v1/invites/' . $invite2->id . '/accept');

        $response->assertNotFound();
    }
    public function test_cant_accept_not_your_own_invite()
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->invited(User::factory())->team(Team::factory())->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/invites/' . $invite->id . '/accept');

        $response->assertForbidden();
    }
    public function test_can_accept_pending_invite()
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->invited($user)->team($team = Team::factory()->create())->create();
        Sanctum::actingAs($user);

        $this->assertEquals(Invite::STATUS_PENDING, $invite->getStatus());
        $this->assertFalse($user->teams()->exists());

        $response = $this->postJson('/api/v1/invites/' . $invite->id . '/accept');

        $response->assertNoContent();
        $invite->refresh();
        $this->assertEquals(Invite::STATUS_ACCEPTED, $invite->getStatus());
        $user->refresh();
        $this->assertEquals(1, $user->teams->count());
        $this->assertTrue($team->is($user->teams[0]));
    }

    public function test_cant_decline_not_pending_invite()
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->accepted()->invited($user)->team(Team::factory())->create();
        $invite2 = Invite::factory()->declined()->invited($user)->team(Team::factory())->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/invites/' . $invite->id . '/decline');

        $response->assertNotFound();

        $response = $this->postJson('/api/v1/invites/' . $invite2->id . '/decline');

        $response->assertNotFound();
    }
    public function test_cant_decline_not_your_own_invite()
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->invited(User::factory())->team(Team::factory())->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/invites/' . $invite->id . '/decline');

        $response->assertForbidden();
    }
    public function test_can_decline_pending_invite()
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->invited($user)->team($team = Team::factory())->create();
        Sanctum::actingAs($user);

        $this->assertEquals(Invite::STATUS_PENDING, $invite->getStatus());
        $this->assertFalse($user->teams()->exists());

        $response = $this->postJson('/api/v1/invites/' . $invite->id . '/decline');

        $response->assertNoContent();
        $invite->refresh();
        $this->assertEquals(Invite::STATUS_DECLINED, $invite->getStatus());
        $this->assertFalse($user->teams()->exists());
    }
}
