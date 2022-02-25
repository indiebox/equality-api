<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Project\LeaderNominationCollection;
use App\Models\Project;

class LeaderNominationController extends Controller
{
    public function index(Project $project)
    {
        $nominations = $project->leaderNominations()
            ->with('nominated')
            ->get()
            ->groupBy('nominated_id');

        return new LeaderNominationCollection($nominations);
    }
}
