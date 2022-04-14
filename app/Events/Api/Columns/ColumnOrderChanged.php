<?php

namespace App\Events\Api\Columns;

use App\Models\Column;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ColumnOrderChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $column;

    public $after;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Column $column, $after)
    {
        $this->column = $column;
        $this->after = $after;
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
        return 'order-changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->column->id,
            'after' => $this->after instanceof Column
                ? $this->after->id
                : 0,
        ];
    }
}
