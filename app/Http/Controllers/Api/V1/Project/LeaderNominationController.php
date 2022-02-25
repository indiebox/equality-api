<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\User;

class LeaderNominationController extends Controller
{
    /**
     * Nominate the user to the project leader.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Project $project, User $leader)
    {
        //
    }
}
