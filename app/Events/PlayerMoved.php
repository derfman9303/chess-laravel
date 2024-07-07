<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $key;
    public $board;
    public $pieces;
    public $turn;
    public $oldR;
    public $oldS;
    public $newR;
    public $newS;

    public function __construct($key, $board, $pieces, $turn, $oldR, $oldS, $newR, $newS)
    {
        $this->key    = $key;
        $this->board  = $board;
        $this->pieces = $pieces;
        $this->turn   = $turn;
        $this->oldR   = $oldR;
        $this->oldS   = $oldS;
        $this->newR   = $newR;
        $this->newS   = $newS;
    }

    public function broadcastOn()
    {
        return new Channel('privatematch-' . $this->key);
    }

    public function broadcastAs() {
        return 'playerMoved';
    }
}
