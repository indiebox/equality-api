<?php

namespace App\Listeners;

class InvalidateUserTokens
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->user->tokens()->delete();
    }
}
