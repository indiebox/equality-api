<?php

namespace App\Broadcasting;

use App\Models\Project;
use App\Models\User;

class ProjectChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Project  $project
     * @return array|bool
     */
    public function join(User $user, Project $project)
    {
        return $project->team->isMember($user);
    }
}
