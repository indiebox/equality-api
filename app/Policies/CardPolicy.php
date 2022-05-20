<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use App\Services\Contracts\Modules\KanbanService;
use Illuminate\Auth\Access\HandlesAuthorization;

class CardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user, Column $column)
    {
        return $column->team->isMember($user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Card  $card
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Card $card)
    {
        return $card->team->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Column $column)
    {
        return $column->team->isMember($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Card  $card
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Card $card)
    {
        return $card->team->isMember($user);
    }

    /**
     * Determine whether the user can update the card.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Card  $card
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function move(User $user, Card $card, Column $column)
    {
        $baseCondition = $card->team->isMember($user)
            && $column->team->is($card->team);

        return $baseCondition
            && app(KanbanService::class)->canMoveCardToColumn($card, $column);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Card  $card
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Card $card)
    {
        return $card->team->isMember($user);
    }
}
