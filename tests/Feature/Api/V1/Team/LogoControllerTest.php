<?php

namespace Tests\Feature\Api\V1\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_store_logo()
    {
        $teamId = Team::factory()->create()->id;
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $teamId . '/logo');

        $response->assertForbidden();
    }
    public function test_can_store_logo()
    {
        Storage::fake();

        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/teams/' . $team->id . '/logo', ['logo' => UploadedFile::fake()->image('test.jpg')]);

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'logo']);
                });
            });
        $team->refresh();
        Storage::assertExists($team->logo);
    }
    public function test_old_logo_deleted_after_store_new()
    {
        Storage::fake();

        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/teams/' . $team->id . '/logo', ['logo' => UploadedFile::fake()->image('test.jpg')]);
        $team->refresh();
        Storage::assertExists($team->logo);

        $this->postJson('/api/v1/teams/' . $team->id . '/logo', ['logo' => UploadedFile::fake()->image('test2.jpg')]);

        Storage::assertMissing($team->logo);
        $team->refresh();
        Storage::assertExists($team->logo);
    }

    public function test_cant_delete_logo_without_permissions()
    {
        $teamId = Team::factory()->create()->id;
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/teams/' . $teamId . '/logo');

        $response->assertForbidden();
    }
    public function test_can_delete_logo()
    {
        Storage::fake();

        $team = Team::factory()->has(User::factory(), 'members')->create();
        $user = User::first();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/teams/' . $team->id . '/logo', ['logo' => UploadedFile::fake()->image('test.jpg')]);
        $team->refresh();
        Storage::assertExists($team->logo);

        $response = $this->deleteJson('/api/v1/teams/' . $team->id . '/logo');

        $response
            ->assertOk()
            ->assertJson(function ($json) {
                $json->has('data', function ($json) {
                    $json->hasAll(['id', 'name', 'logo']);
                });
            });
        Storage::assertMissing($team->logo);
        $team->refresh();
        $this->assertNull($team->logo);
    }
}
