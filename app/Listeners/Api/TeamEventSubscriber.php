<?php

namespace App\Listeners\Api;

use App\Events\Api\UserLeaveTeam;
use App\Models\Team;
use App\Services\Contracts\Image\ImageService;
use App\Services\Contracts\Projects\LeaderService;

class TeamEventSubscriber
{
    /**
     * Handle user leave events.
     */
    public function handleUserLeave(UserLeaveTeam $event)
    {
        // Delete the team if there are no members left.
        if (!$event->team->members()->exists()) {
            $event->team->delete();
            return;
        }

        /**
         * @var LeaderService
         */
        $leaderService = app(LeaderService::class);
        $leaderService->deleteUserNominations($event->user, $event->team);
    }

    /**
     * Handle team deleting events.
     */
    public function handleTeamDeleting($team)
    {
        // Delete resources associated with team.
        $imageService = app(ImageService::class);
        $imageService->delete($team->logo);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            'eloquent.deleting: ' . Team::class => 'handleTeamDeleting',
            UserLeaveTeam::class => 'handleUserLeave',
        ];
    }
}
