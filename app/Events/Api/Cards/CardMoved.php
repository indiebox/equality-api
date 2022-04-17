<?php

namespace App\Events\Api\Cards;

use App\Models\Card;
use App\Models\Column;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $card;

    public $column;

    public $after;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Card $card, Column $column, $after)
    {
        $this->card = $card;
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
        return new PrivateChannel("boards.{$this->card->board->id}.cards");
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'moved';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->card->id,
            'column' => $this->column->id,
            'after_card' => $this->after instanceof Card
                ? $this->after->id
                : $this->after,
        ];
    }
}
