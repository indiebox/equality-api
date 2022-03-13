<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Column;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ColumnPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Column $column)
    {
        return $column->team->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Board $board)
    {
        return $board->team->isMember($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Column $column)
    {
        return $column->team->isMember($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Column $column)
    {
        return $column->team->isMember($user);
    }
}
