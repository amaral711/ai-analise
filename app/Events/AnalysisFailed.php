<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalysisFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Model $analysis,
        public string $type,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.User.' . $this->analysis->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'analysis.failed';
    }

    public function broadcastWith(): array
    {
        return [
            'id'   => $this->analysis->id,
            'type' => $this->type,
        ];
    }
}
