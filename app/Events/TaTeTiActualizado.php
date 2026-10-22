<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TaTeTiActualizado implements ShouldBroadcast
{
    public function __construct(
        public string $sala,
        public array $tablero,
        public string $turno,
        public string $estado,
        public ?string $ganador = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('tateti.' . $this->sala),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TaTeTiActualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'tablero' => $this->tablero,
            'turno' => $this->turno,
            'estado' => $this->estado,
            'ganador' => $this->ganador,
        ];
    }
}
