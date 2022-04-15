<?php

namespace App\Events\Api\Projects;

use App\Http\Resources\V1\Team\TeamMemberResource;
use App\Models\Project;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaderNominated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;

    public $collection;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Project $project, $collection)
    {
        $this->project = $project;
        $this->collection = $collection;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("projects.{$this->project->id}");
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'leader-nominated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $result = collect();

        foreach ($this->collection as $nomination) {
            $result->add([
                'is_leader' => $nomination['is_leader'],
                'nominated' => new TeamMemberResource($nomination['nominated']),
                'voters' => TeamMemberResource::collection($nomination['voters']),
                'voters_count' => $nomination['voters_count'],
            ]);
        }

        return [
            'nominations' => $result,
        ];
    }
}
