<?php

namespace Tests\Feature\Console\Prunning;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteOldArchivedProjectsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_delete_not_trashed_projects()
    {
        $team = Team::factory()->create();
        Project::factory(4)->team($team)->create();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('projects', 4);
    }

    public function test_cant_delete_trashed_projects_before_time()
    {
        $team = Team::factory()->create();
        Project::factory(2)->team($team)->deleted()->create();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('projects', 2);
    }

    public function test_can_delete_trashed_projects_after_time()
    {
        $team = Team::factory()->create();
        Project::factory()->team($team)->deleted()->create();
        $project1 = Project::factory()->team($team)->create();
        $project2 = Project::factory()->team($team)->create(['deleted_at' => now()->addWeek()]);

        $this->travel(1)->weeks();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('projects', 2);
        $this->assertDatabaseHas('projects', ['id' => $project1->id]);
        $this->assertDatabaseHas('projects', ['id' => $project2->id]);
    }

    public function test_image_deleted_after_prunning()
    {
        Storage::fake();

        $team = Team::factory()->create();
        $file = UploadedFile::fake()->image('image.jpg');
        $file->store('projects');
        Project::factory()->team($team)->deleted()->create(['image' => 'projects/' . $file->hashName()]);
        Storage::assertExists('projects/' . $file->hashName());

        $this->travel(1)->weeks();

        Artisan::call('model:prune');

        $this->assertDatabaseCount('projects', 0);
        Storage::assertMissing('projects/' . $file->hashName());
    }
}
