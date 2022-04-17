<?php

namespace App\Events\Api\Cards;

use App\Models\Card;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $card;

    public $afterCard;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Card $card, $afterCard)
    {
        $this->card = $card;
        $this->afterCard = $afterCard;
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
        return 'created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'card' => [
                'id' => $this->card->id,
                'name' => $this->card->name,
                'description' => $this->card->description,
                'created_at' => $this->card->created_at,
                'updated_at' => $this->card->updated_at,
            ],
            'after_card' => $this->afterCard instanceof Card
                ? $this->afterCard->id
                : $this->afterCard,
        ];
    }
}
