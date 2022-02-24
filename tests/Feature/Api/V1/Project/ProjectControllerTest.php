<?php

namespace Tests\Feature\Api\V1\Project;

use App\Http\Resources\V1\Project\ProjectResource;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_view_in_not_your_team()
    {
        $project = Project::factory()->team(Team::factory())->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id);

        $response->assertForbidden();
    }
    public function test_can_view()
    {
        $team = Team::factory()->create();
        $project = Project::factory()->team($team)->create();
        $user = User::factory()->hasAttached($team)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/' . $project->id);

        $response
            ->assertOk()
            ->assertJson((new ProjectResource($project))->response()->getData(true));
    }
}
