<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberCountUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function broadcastOn()
    {
        return new Channel('members'); // public channel
    }

    public function broadcastAs()
    {
        return 'MemberCountUpdated'; // custom event name
    }

    public function broadcastWith()
    {
        return [
            'count' => $this->count,
            'time' => now()->toDateTimeString()
        ];
    }
}
