<?php

use App\Broadcasting\CardChannel;
use App\Broadcasting\ColumnChannel;
use App\Broadcasting\ProjectChannel;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('projects.{project}', ProjectChannel::class);

Broadcast::channel('boards.{board}.columns', ColumnChannel::class);
Broadcast::channel('boards.{board}.cards', CardChannel::class);
