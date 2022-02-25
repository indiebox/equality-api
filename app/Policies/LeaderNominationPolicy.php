<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaderNominationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user, Project $project)
    {
        return $project->team->isMember($user);
    }

    /**
     * Determine whether the user can nominate user to the project leader.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @param  \App\Models\User  $nominated
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function nominate(User $user, Project $project, User $nominated)
    {
        $project->team->load('members');

        return $project->team->isMember($user)
            && $project->team->isMember($nominated);
    }
}
