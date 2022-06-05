<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Authorize get modules of board.
     * @param User $user
     * @param Board $board
     */
    public function viewAny(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }

    /**
     * Authorize get module settings.
     * @param User $user
     * @param Board $board
     */
    public function viewSettings(User $user, Board $board)
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
