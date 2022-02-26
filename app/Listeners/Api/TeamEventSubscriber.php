<?php

namespace App\Listeners\Api;

use App\Events\Api\UserLeaveTeam;
use App\Models\Team;
use App\Services\Contracts\Image\ImageService;
use Illuminate\Database\Eloquent\Builder;

class TeamEventSubscriber
{
    /**
     * Handle user leave events.
     */
    public function handleUserLeave(UserLeaveTeam $event) {
        // Delete the team if there are no members left.
        if (!$event->team->members()->exists()) {
            $event->team->delete();
            return;
        }

        // Delete leader nominations for all projects of the team where
        // voter or nominated user is user that leaves team.
        $event->team->projectsLeaderNominations()
            ->where(function (Builder $query) use ($event) {
                $query->where('voter_id', $event->user->id)
                    ->orWhere('nominated_id', $event->user->id);
            })
            ->delete();

        // Clear leader for all projects of the team where
        // leader is user that leaves team.
        $event->team->projects()
            ->where('leader_id', $event->user->id)
            ->update(['leader_id' => null]);
    }

    /**
     * Handle team deleting events.
     */
    public function handleTeamDeleting($team) {
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
