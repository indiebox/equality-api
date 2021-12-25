<?php

namespace App\Events\Api;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeaveTeam
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $team;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $team)
    {
        $this->user = $user;
        $this->team = $team;
    }
}
