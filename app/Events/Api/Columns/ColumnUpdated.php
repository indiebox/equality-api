<?php

namespace App\Events\Api\Columns;

use App\Models\Column;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ColumnUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $column;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("boards.{$this->column->board_id}.columns");
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'column' => [
                'id' => $this->column->id,
                'name' => $this->column->name,
                'created_at' => $this->column->created_at,
                'updated_at' => $this->column->updated_at,
            ],
        ];
    }
}
