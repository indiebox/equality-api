<?php

namespace App\Broadcasting;

use App\Models\Board;
use App\Models\User;

class ColumnChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Board  $board
     * @return array|bool
     */
    public function join(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }
}
