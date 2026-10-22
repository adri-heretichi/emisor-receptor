<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartidaTaTeTi extends Model
{
    protected $table = 'partida_ta_te_tis';

    protected $fillable = [
        'codigo_sala',
        'jugador_x',
        'jugador_o',
        'tablero',
        'turno',
        'estado',
        'ganador',
        'tipo',
    ];

    protected $casts = [
        'tablero' => 'array',
    ];
}
