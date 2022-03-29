<?php

namespace Tests\Feature\Api\V1\Project;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_store_image()
    {
        $team = Team::factory()->create();
        $projectId = Project::factory()->team($team)->create()->id;
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $projectId . '/image');

        $response->assertForbidden();
    }
    public function test_can_store_image()
    {
        Storage::fake();

        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/' . $project->id . '/image', [
            'image' => UploadedFile::fake()->image('test.jpg'),
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'image']]);
        $project->refresh();
        Storage::assertExists($project->image);
    }
    public function test_old_image_deleted_after_store_new()
    {
        Storage::fake();

        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects/' . $project->id . '/image', ['image' => UploadedFile::fake()->image('test.jpg')]);
        $project->refresh();
        Storage::assertExists($project->image);

        $this->postJson('/api/v1/projects/' . $project->id . '/image', ['image' => UploadedFile::fake()->image('test2.jpg')]);

        Storage::assertMissing($project->image);
        $project->refresh();
        Storage::assertExists($project->image);
    }

    public function test_cant_delete_image_without_permissions()
    {
        $team = Team::factory()->create();
        $projectId = Project::factory()->team($team)->create()->id;
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/projects/' . $projectId . '/image');

        $response->assertForbidden();
    }
    public function test_can_delete_image()
    {
        Storage::fake();

        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects/' . $project->id . '/image', ['image' => UploadedFile::fake()->image('test.jpg')]);
        $project->refresh();
        Storage::assertExists($project->image);

        $response = $this->deleteJson('/api/v1/projects/' . $project->id . '/image');

        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'image']]);
        Storage::assertMissing($project->image);
        $project->refresh();
        $this->assertNull($project->image);
    }
}
