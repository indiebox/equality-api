<?php

namespace App\Policies;

use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any invites for team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user, Team $team)
    {
        return $team->isMember($user);
    }

    /**
     * Determine whether the user can create invites for team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Team $team)
    {
        return $team->isMember($user);
    }

    /**
     * Determine whether the user can revoke invite.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Invite $invite)
    {
        return $invite->team->isMember($user);
    }

    /**
     * Determine whether the user can accept invite.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function accept(User $user, Invite $invite)
    {
        return $invite->invited_id == $user->id;
    }

    /**
     * Determine whether the user can decline invite.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invite  $invite
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function decline(User $user, Invite $invite)
    {
        return $invite->invited_id == $user->id;
    }
}
