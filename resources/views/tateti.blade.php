
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ta-Te-Ti Online</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fce7f3;
            text-align: center;
            padding: 30px;
            margin: 0;
        }

        h1 {
            color: #9d174d;
            margin-top: 30px;
        }

        .subtitulo {
            color: #831843;
            font-size: 17px;
        }

        .opciones {
            max-width: 420px;
            margin: 35px auto;
        }

        .boton-crear {
            display: block;
            width: 100%;
            box-sizing: border-box;
            background: #db2777;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px 28px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            margin: 15px 0;
        }

        .boton-crear:hover {
            background: #9d174d;
        }

        .boton-privado {
            background: #831843;
        }

        .boton-privado:hover {
            background: #500724;
        }

        .descripcion {
            color: #831843;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .tablero {
            display: grid;
            grid-template-columns: repeat(3, 90px);
            gap: 8px;
            justify-content: center;
            margin: 30px auto;
        }

        .casilla {
            width: 90px;
            height: 90px;
            background: white;
            border: 2px solid #ec4899;
            border-radius: 12px;
            font-size: 42px;
            font-weight: bold;
            color: #9d174d;
        }

        .pie {
            color: #831843;
            margin-top: 20px;
        }

        .mensaje {
            max-width: 420px;
            margin: 20px auto;
            padding: 12px;
            background: white;
            border-radius: 10px;
            color: #9d174d;
        }
    </style>
</head>

<body>

    <h1>🎮 Ta-Te-Ti Online</h1>

    <p class="subtitulo">
        Juego multijugador con Laravel y WebSockets
    </p>

    @if (session('mensaje'))
        <p class="mensaje">
            {{ session('mensaje') }}
        </p>
    @endif

    @if (session('error'))
        <p class="mensaje">
            {{ session('error') }}
        </p>
    @endif

    <div class="opciones">

        <!-- Buscar un contrincante automáticamente -->
        <form action="{{ route('tateti.buscar') }}" method="POST">
            @csrf

            <button type="submit" class="boton-crear">
                🔎 Buscar contrincante
            </button>
        </form>

        <p class="descripcion">
            Entrá a la sala de espera y el sistema te conectará
            automáticamente con otro jugador disponible.
        </p>

        <!-- Crear una sala privada -->
        <form action="{{ route('tateti.crear') }}" method="POST">
            @csrf

            <button type="submit" class="boton-crear boton-privado">
                🔗 Crear sala privada
            </button>
        </form>

        <p class="descripcion">
            Creá una partida privada y compartí el enlace
            con un amigo para jugar juntos.
        </p>

    </div>

    <h2>Dos jugadores, una partida</h2>

    <!-- Tablero decorativo de la pantalla inicial -->
    <div class="tablero">
        @for ($i = 0; $i < 9; $i++)
            <button type="button" class="casilla" disabled></button>
        @endfor
    </div>

    <p class="pie">
        Jugador X contra Jugador O
    </p>

</body>
</html>
