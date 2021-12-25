<?php

namespace Tests\Feature\Api\V1\Team;

use App\Events\Api\UserLeaveTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_any_unauthorized()
    {
        $response = $this->getJson('/api/v1/teams');

        $response->assertUnauthorized();
    }
    public function test_can_view_any()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams');

        $response->assertOk();
    }

    public function test_cant_view_without_permissions()
    {
        $user = User::factory()->create();
        $teamId = Team::factory()->create()->id;
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $teamId);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $teamId = Team::factory()->has(User::factory(), 'members')->create()->id;
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/teams/' . $teamId);

        $response->assertOk();
    }

    public function test_can_store()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'Team name',
            'description' => 'Test',
        ];

        $response = $this->postJson('/api/v1/teams', $data);

        $response->assertCreated();
        $this->assertDatabaseCount('teams', 1);
        $this->assertDatabaseHas('teams', $data);
        $this->assertDatabaseCount('team_user', 1);
        $this->assertDatabaseHas('team_user', ['user_id' => $user->id, 'is_creator' => true]);
    }

    public function test_cant_update_without_permissions()
    {
        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
            'description' => 'New description',
        ];

        $response = $this->patchJson('/api/v1/teams/' . $team->id, $data);

        $response->assertForbidden();
    }
    public function test_can_update()
    {
        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);
        $data = [
            'name' => 'New name',
            'description' => 'New description',
        ];

        $response = $this->patchJson('/api/v1/teams/' . $team->id, $data);

        $response->assertOk();
        $this->assertDatabaseHas('teams', array_merge($data, ['url' => $team->url]));
    }

    public function test_cant_leave()
    {
        $user = User::factory()->create();
        $team = Team::factory()->has(User::factory(), 'members')->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertForbidden();
    }
    public function test_can_leave()
    {
        Event::fake();
        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        Event::assertDispatched(UserLeaveTeam::class);
    }

    public function test_team_deleted_after_last_member_leave()
    {
        $team = Team::factory()->has(User::factory()->count(2), 'members')->create();
        $user = User::all()->first();
        Sanctum::actingAs($user);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();

        $user = User::all()->last();
        Sanctum::actingAs($user);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        $this->assertDatabaseCount('teams', 0);
    }
    public function test_logo_deleted_after_team_deleting()
    {
        Storage::fake();

        $team = Team::factory()->has(User::factory()->count(2), 'members')->create();
        $user = User::all()->first();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/teams/' . $team->id . '/logo', ['logo' => UploadedFile::fake()->image('test.jpg')]);
        $team->refresh();
        Storage::assertExists($team->logo);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        Storage::assertExists($team->logo);

        $user = User::all()->last();
        Sanctum::actingAs($user);

        $this->assertDatabaseCount('teams', 1);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/leave');

        $response->assertNoContent();
        $this->assertDatabaseCount('teams', 0);
        Storage::assertMissing($team->logo);
    }
}
