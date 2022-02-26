<?php

namespace App\Services\Contracts\Projects;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;

interface LeaderService {
    /**
     * Delete leader nominations for all projects of the team where voter or nominated user is user that leaves team.
     * @param User $user
     * @param Team $team
     */
    public function deleteAssociatedNominations(User $user, Team $team);

    /**
     * Recalculate the leader of the project.
     * @param Project $project
     */
    public function recalculateProjectLeader(Project $project);

    /**
     * Recalculate the leader of the team projects.
     * @param Team $team
     * @return int The number of affected rows.
     */
    public function recalculateProjectsLeaderInTeam(Team $team);
}
