<?php

namespace Tests\Feature\Api\V1\Team;

use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InviteControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_in_other_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Invite::factory()->team(Team::factory())->invited(User::factory())->create();
        Invite::factory()->accepted()->team(Team::factory())->invited(User::factory())->create();
        Invite::factory()->declined()->team(Team::factory())->invited(User::factory())->create();

        $response = $this->getJson('/api/v1/teams/' . $team->id . '/invites');

        $response->assertForbidden();
    }
    public function test_can_view_any()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);
        Invite::factory()->team($team)->invited(User::factory())->create();
        Invite::factory()->accepted()->team($team)->invited(User::factory())->create();
        Invite::factory()->declined()->team($team)->invited(User::factory())->create();
        $invites = Invite::all();

        // Filter all.
        $response = $this->getJson('/api/v1/teams/' . $team->id . '/invites');

        $response
            ->assertOk()
            ->assertJsonCount($invites->count(), 'data')
            ->assertJsonStructure(['data' => [['id', 'status', 'inviter', 'invited']]]);

        // Filter pending.
        $response = $this->getJson('/api/v1/teams/' . $team->id . '/invites?filter[status]=pending');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $invites[0]->id)
            ->assertJsonStructure(['data' => [['id', 'status', 'inviter', 'invited']]]);

        // Filter accepted.
        $response = $this->getJson('/api/v1/teams/' . $team->id . '/invites?filter[status]=accepted');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $invites[1]->id)
            ->assertJsonStructure(['data' => [['id', 'status', 'inviter', 'invited']]]);

        // Filter declined.
        $response = $this->getJson('/api/v1/teams/' . $team->id . '/invites?filter[status]=declined');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $invites[2]->id)
            ->assertJsonStructure(['data' => [['id', 'status', 'inviter', 'invited']]]);
    }

    public function test_cant_invite_in_not_your_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/invites');

        $response->assertForbidden();
    }
    public function test_cant_invite_not_existing_user()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/invites', ['email' => 'test@mail.ru']);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => trans('validation.invalid_user')]);
    }
    public function test_cant_invite_member_of_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $user2 = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/invites', ['email' => $user2->email]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => trans('validation.is_member_of_team')]);
    }
    public function test_cant_invite_if_already_invited()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $user2 = User::factory()->create();
        Invite::factory()->team($team)->invited($user2)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/invites', ['email' => $user2->email]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => trans('validation.already_invited')]);
    }
    public function test_can_invite()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/invites', ['email' => $user2->email]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'inviter' => ['id', 'name', 'email'],
                    'invited' => ['id', 'name', 'email'],
                ],
            ]);
        $this->assertDatabaseHas('invites', ['team_id' => $team->id, 'inviter_id' => $user->id, 'invited_id' => $user2->id]);
    }

    public function test_cant_destroy_invite_in_not_your_team()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $invite = Invite::factory()->team($team)->invited(User::factory())->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/invites/' . $invite->id);

        $response->assertForbidden();
    }
    public function test_cant_destroy_not_pending_invite()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $invite = Invite::factory()->accepted()->invited($user)->team($team)->create();
        $invite2 = Invite::factory()->declined()->invited($user)->team($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/invites/' . $invite->id);

        $response->assertNotFound();

        $response = $this->deleteJson('/api/v1/invites/' . $invite2->id);

        $response->assertNotFound();
    }
    public function test_can_destroy_pending_invite()
    {
        $team = Team::factory()->create();
        $user = User::factory()->hasAttached($team)->create();
        $invite = Invite::factory()->invited(User::factory())->team($team)->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/invites/' . $invite->id);

        $response->assertNoContent();
        $this->assertDatabaseCount('invites', 0);
    }
}
