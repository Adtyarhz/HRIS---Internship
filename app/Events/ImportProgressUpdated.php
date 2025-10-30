<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $importId;
    public $progress;

    public function __construct($importId, $progress)
    {
        $this->importId = $importId;
        $this->progress = $progress;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('import.' . $this->importId);
    }

    public function broadcastAs()
    {
        return 'progress.updated';
    }
}