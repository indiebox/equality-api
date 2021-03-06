<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Events\Api\Projects\LeaderNominated;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Project\LeaderNominationCollection;
use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\User;
use App\Services\Contracts\Projects\LeaderService;

class LeaderNominationController extends Controller
{
    /**
     * Get all leader nominations in project.
     * @param \App\Models\Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LeaderService $leaderService, Project $project)
    {
        return new LeaderNominationCollection($leaderService->makeNominationsCollection($project));
    }

    /**
     * Nominate the user to the project leader.
     * @param \App\Services\Contracts\Projects\LeaderService $leaderService
     * @param \App\Models\Project $project
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function nominate(LeaderService $leaderService, Project $project, User $user)
    {
        LeaderNomination::updateOrCreate(
            ['voter_id' => auth()->id(), 'project_id' => $project->id],
            ['nominated_id' => $user->id]
        );

        $leaderService->determineNewLeader($project);

        $nominations = $leaderService->makeNominationsCollection($project);

        broadcast(new LeaderNominated($project, $nominations))->toOthers();

        return new LeaderNominationCollection($nominations);
    }
}
