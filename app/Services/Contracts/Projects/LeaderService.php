<?php

namespace App\Services\Contracts\Projects;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;

interface LeaderService
{
    /**
     * Delete leader nominations for all projects of the team where voter or nominated user
     * is user that leaves team and determine new leaders for affected projects.
     * @param User $user
     * @param Team $team
     */
    public function deleteUserNominations(User $user, Team $team);

    /**
     * Determine the new leader of the project.
     * @param Project $project
     */
    public function determineNewLeader(Project $project);
}
