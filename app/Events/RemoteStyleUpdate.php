<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RemoteStyleUpdate implements ShouldBroadcast
{
    public string $newText;
    public string $newStyle;

    public function __construct(string $newText, string $newStyle)
    {
        $this->newText = $newText;
        $this->newStyle = $newStyle;
    }

    // Canal PUBLICO (no requiere autenticacion)
    public function broadcastOn(): array
    {
        return [new Channel('style-channel')];
    }

    // Nombre del evento en el frontend
    public function broadcastAs(): string
    {
        return 'RemoteStyleUpdate';
    }
}
