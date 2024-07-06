<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StartGame implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $key;
    public $playerOneIsWhite;
    public $timeLimit;

    public function __construct($key, $playerOneIsWhite, $timeLimit)
    {
        $this->key = $key;
        $this->playerOneIsWhite = $playerOneIsWhite;
        $this->timeLimit = $timeLimit;
    }

    public function broadcastOn()
    {
        return new Channel('privatematch-' . $this->key);
    }

    public function broadcastAs() {
        return 'startGame';
    }
}
