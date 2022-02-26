<?php

namespace App\Http\Controllers\Api\V1\Project;

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
    public function index(Project $project)
    {
        $nominations = $project->leaderNominations()
            ->with('nominated')
            ->get()
            ->groupBy('nominated_id');

        return new LeaderNominationCollection($nominations);
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

        $leaderService->recalculateProjectLeader($project);

        return response('', 204);
    }
}
