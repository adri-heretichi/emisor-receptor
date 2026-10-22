<?php 

namespace App\Http\Controllers;

use App\Events\TaTeTiActualizado;
use App\Models\PartidaTaTeTi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaTeTiController extends Controller
{
    // Pantalla principal del Ta-Te-Ti.
    public function index()
    {
        return view('tateti');
    }

    // Crear una sala privada para compartir por enlace.
    public function crearSala(Request $request)
    {
        do {
            $codigo = strtoupper(Str::random(6));
        } while (
            PartidaTaTeTi::where('codigo_sala', $codigo)->exists()
        );

        $jugador = Str::random(40);

        PartidaTaTeTi::create([
            'codigo_sala' => $codigo,
            'jugador_x' => $jugador,
            'jugador_o' => null,
            'tablero' => array_fill(0, 9, ''),
            'turno' => 'X',
            'estado' => 'esperando',
            'ganador' => null,
            'tipo' => 'privada',
        ]);

        $request->session()->put("tateti.$codigo", $jugador);

        return redirect()->route('tateti.sala', [
            'codigo' => $codigo,
        ]);
    }

    // Mostrar la sala e identificar al jugador.
    public function mostrarSala(Request $request, string $codigo)
    {
        $partida = PartidaTaTeTi::where('codigo_sala', $codigo)
            ->firstOrFail();

        $jugador = $request->session()->get("tateti.$codigo");

        $simbolo = 'ESPECTADOR';

        if ($jugador && $jugador === $partida->jugador_x) {
            $simbolo = 'X';
        } elseif ($jugador && $jugador === $partida->jugador_o) {
            $simbolo = 'O';
        }

        return view('tateti-sala', [
            'partida' => $partida,
            'simbolo' => $simbolo,
        ]);
    }

    // Unirse como jugador O a una sala privada.
    public function unirseSala(Request $request, string $codigo)
    {
        $resultado = DB::transaction(function () use ($request, $codigo) {
            $partida = PartidaTaTeTi::where('codigo_sala', $codigo)
                ->lockForUpdate()
                ->firstOrFail();

            $jugadorActual = $request->session()
                ->get("tateti.$codigo");

            if (
                $jugadorActual &&
                (
                    $jugadorActual === $partida->jugador_x ||
                    $jugadorActual === $partida->jugador_o
                )
            ) {
                return [
                    'codigo' => $codigo,
                    'partida' => null,
                ];
            }

            if ($partida->tipo !== 'privada') {
                return ['error' => 'Esta sala utiliza emparejamiento automático.'];
            }

            if ($partida->jugador_o !== null) {
                return ['error' => 'La sala ya tiene dos jugadores.'];
            }

            if ($partida->estado !== 'esperando') {
                return ['error' => 'La sala ya no está disponible.'];
            }

            $jugadorO = Str::random(40);

            $partida->update([
                'jugador_o' => $jugadorO,
                'estado' => 'jugando',
            ]);

            $request->session()->put(
                "tateti.$codigo",
                $jugadorO
            );

            return [
                'codigo' => $codigo,
                'partida' => $partida,
            ];
        });

        if (isset($resultado['error'])) {
            return redirect()
                ->route('tateti.sala', ['codigo' => $codigo])
                ->with('error', $resultado['error']);
        }

        if ($resultado['partida'] !== null) {
            $this->notificarPartida($resultado['partida']);
        }

        return redirect()->route('tateti.sala', [
            'codigo' => $codigo,
        ]);
    }

    // Buscar automáticamente otro jugador disponible.
    public function buscarContrincante(Request $request)
    {
        $token = $request->session()->get('tateti.match_token');

        if (!$token) {
            $token = Str::random(40);
            $request->session()->put('tateti.match_token', $token);
        }

        $seUnio = false;

        $codigo = Cache::lock('tateti_emparejamiento', 15)
            ->block(5, function () use ($request, $token, &$seUnio) {
                return DB::transaction(function () use (
                    $request,
                    $token,
                    &$seUnio
                ) {
                    // Recuperar una partida pública activa.
                    $actual = PartidaTaTeTi::where('tipo', 'publica')
                        ->where(function ($consulta) use ($token) {
                            $consulta->where('jugador_x', $token)
                                ->orWhere('jugador_o', $token);
                        })
                        ->whereIn('estado', ['esperando', 'jugando'])
                        ->where('updated_at', '>=', now()->subMinutes(10))
                        ->orderByDesc('id')
                        ->first();

                    if ($actual) {
                        $request->session()->put(
                            "tateti.{$actual->codigo_sala}",
                            $token
                        );

                        return $actual->codigo_sala;
                    }

                    // Buscar una sala pública reciente sin rival.
                    $partida = PartidaTaTeTi::where('tipo', 'publica')
                        ->where('estado', 'esperando')
                        ->whereNull('jugador_o')
                        ->where('jugador_x', '!=', $token)
                        ->where('created_at', '>=', now()->subMinutes(10))
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();

                    if ($partida) {
                        $partida->update([
                            'jugador_o' => $token,
                            'estado' => 'jugando',
                        ]);

                        $request->session()->put(
                            "tateti.{$partida->codigo_sala}",
                            $token
                        );

                        $seUnio = true;

                        return $partida->codigo_sala;
                    }

                    // Si nadie espera, crear una sala pública nueva.
                    do {
                        $nuevoCodigo = strtoupper(Str::random(6));
                    } while (
                        PartidaTaTeTi::where(
                            'codigo_sala',
                            $nuevoCodigo
                        )->exists()
                    );

                    PartidaTaTeTi::create([
                        'codigo_sala' => $nuevoCodigo,
                        'jugador_x' => $token,
                        'jugador_o' => null,
                        'tablero' => array_fill(0, 9, ''),
                        'turno' => 'X',
                        'estado' => 'esperando',
                        'ganador' => null,
                        'tipo' => 'publica',
                    ]);

                    $request->session()->put(
                        "tateti.$nuevoCodigo",
                        $token
                    );

                    return $nuevoCodigo;
                });
            });

        // Avisar al jugador X cuando se encuentra contrincante.
        if ($seUnio) {
            $partida = PartidaTaTeTi::where(
                'codigo_sala',
                $codigo
            )->firstOrFail();

            $this->notificarPartida($partida);
        }

        return redirect()->route('tateti.sala', [
            'codigo' => $codigo,
        ]);
    }

    // Registrar y validar una jugada.
    public function jugar(Request $request, string $codigo)
    {
        $datos = $request->validate([
            'posicion' => ['required', 'integer', 'between:0,8'],
        ]);

        $resultado = DB::transaction(function () use (
            $request,
            $codigo,
            $datos
        ) {
            $partida = PartidaTaTeTi::where('codigo_sala', $codigo)
                ->lockForUpdate()
                ->firstOrFail();

            $jugador = $request->session()->get("tateti.$codigo");

            if ($jugador && $jugador === $partida->jugador_x) {
                $simbolo = 'X';
            } elseif (
                $jugador &&
                $partida->jugador_o !== null &&
                $jugador === $partida->jugador_o
            ) {
                $simbolo = 'O';
            } else {
                abort(403, 'No pertenecés a esta partida.');
            }

            if ($partida->estado !== 'jugando') {
                return ['error' => 'La partida no está en curso.'];
            }

            if ($partida->turno !== $simbolo) {
                return ['error' => 'No es tu turno.'];
            }

            $tablero = $partida->tablero;
            $posicion = $datos['posicion'];

            if ($tablero[$posicion] !== '') {
                return ['error' => 'Esa casilla ya está ocupada.'];
            }

            $tablero[$posicion] = $simbolo;

            $lineas = [
                [0, 1, 2],
                [3, 4, 5],
                [6, 7, 8],
                [0, 3, 6],
                [1, 4, 7],
                [2, 5, 8],
                [0, 4, 8],
                [2, 4, 6],
            ];

            $ganador = null;

            foreach ($lineas as [$a, $b, $c]) {
                if (
                    $tablero[$a] !== '' &&
                    $tablero[$a] === $tablero[$b] &&
                    $tablero[$b] === $tablero[$c]
                ) {
                    $ganador = $simbolo;
                    break;
                }
            }

            if ($ganador !== null) {
                $estado = 'finalizada';
            } elseif (!in_array('', $tablero, true)) {
                $estado = 'empate';
            } else {
                $estado = 'jugando';
            }

            $partida->update([
                'tablero' => $tablero,
                'turno' => $simbolo === 'X' ? 'O' : 'X',
                'estado' => $estado,
                'ganador' => $ganador,
            ]);

            return ['partida' => $partida];
        });

        if (isset($resultado['error'])) {
            return back()->with('error', $resultado['error']);
        }

        $this->notificarPartida($resultado['partida']);

        return back();
    }

    // Reiniciar una partida finalizada o empatada.
    public function reiniciar(Request $request, string $codigo)
    {
        $resultado = DB::transaction(function () use ($request, $codigo) {
            $partida = PartidaTaTeTi::where('codigo_sala', $codigo)
                ->lockForUpdate()
                ->firstOrFail();

            $jugador = $request->session()->get("tateti.$codigo");

            if (
                !$jugador ||
                !in_array(
                    $jugador,
                    array_filter([
                        $partida->jugador_x,
                        $partida->jugador_o,
                    ]),
                    true
                )
            ) {
                abort(403, 'No pertenecés a esta partida.');
            }

            if (
                !in_array(
                    $partida->estado,
                    ['finalizada', 'empate'],
                    true
                )
            ) {
                return null;
            }

            $partida->update([
                'tablero' => array_fill(0, 9, ''),
                'turno' => 'X',
                'estado' => 'jugando',
                'ganador' => null,
            ]);

            return $partida;
        });

        if ($resultado === null) {
            return back()->with(
                'error',
                'La partida todavía no terminó.'
            );
        }

        $this->notificarPartida($resultado);

        return redirect()->route('tateti.sala', [
            'codigo' => $codigo,
        ]);
    }

    // Enviar el estado de la partida mediante WebSockets.
    private function notificarPartida(PartidaTaTeTi $partida): void
    {
        broadcast(new TaTeTiActualizado(
            $partida->codigo_sala,
            $partida->tablero,
            $partida->turno,
            $partida->estado,
            $partida->ganador
        ));
    }
    
public function cancelarBusqueda(Request $request)
{
    $token = $request->session()->get('tateti.match_token');

    if (!$token) {
        return redirect()->route('tateti');
    }

    DB::transaction(function () use ($token) {
        $partidas = PartidaTaTeTi::where('tipo', 'publica')
            ->where('jugador_x', $token)
            ->where('estado', 'esperando')
            ->whereNull('jugador_o')
            ->lockForUpdate()
            ->get();

        foreach ($partidas as $partida) {
            $partida->delete();
        }
    });

    return redirect()->route('tateti')
        ->with('mensaje', 'Búsqueda cancelada.');
}

public function buscarOtraPartida(Request $request)
{
    $token = $request->session()->get('tateti.match_token');

    if ($token) {
        $partidaActiva = PartidaTaTeTi::where('tipo', 'publica')
            ->where(function ($consulta) use ($token) {
                $consulta->where('jugador_x', $token)
                    ->orWhere('jugador_o', $token);
            })
            ->where('estado', 'jugando')
            ->exists();

        if ($partidaActiva) {
            return back()->with(
                'error',
                'Primero debe finalizar la partida actual.'
            );
        }
    }

    return $this->buscarContrincante($request);
}

}
