<?php

namespace App\Broadcasting;

use App\Models\Card;
use App\Models\User;

class CardChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Card  $card
     * @return array|bool
     */
    public function join(User $user, Card $card)
    {
        return $card->team->isMember($user);
    }
}
