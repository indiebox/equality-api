<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }

    public function enableKanban(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }

    public function disableKanban(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }
}
