<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RemoteStyleUpdate implements ShouldBroadcast
{
    public string $newText;
    public string $newStyle;

    public ?int $triggeredById;
    public ?string $triggeredByIp;

    public function __construct(
        string $newText,
        string $newStyle,
        ?int $triggeredById = null,
        ?string $triggeredByIp = null
    ) {
        $this->newText = $newText;
        $this->newStyle = $newStyle;
        $this->triggeredById = $triggeredById;
        $this->triggeredByIp = $triggeredByIp;
    }

    // Canal público
    public function broadcastOn(): array
    {
        return [new Channel('style-channel')];
    }

    // Nombre del evento
    public function broadcastAs(): string
    {
        return 'RemoteStyleUpdate';
    }
    
public function broadcastWith(): array
{
    return [
        'newText' => $this->newText,
        'newStyle' => $this->newStyle,
    ];
}

}
