
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sala {{ $partida->codigo_sala }} - Ta-Te-Ti</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
        }

        .informacion,
        .compartir-sala {
            background: white;
            border-radius: 12px;
            padding: 20px;
            max-width: 420px;
            margin: 20px auto;
        }

        .informacion h2 {
            color: #9d174d;
        }

        .tablero {
            display: grid;
            grid-template-columns: repeat(3, 90px);
            gap: 8px;
            justify-content: center;
            margin: 25px auto;
        }

        .tablero form {
            width: 90px;
            height: 90px;
            margin: 0;
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
            cursor: pointer;
        }

        .casilla:disabled {
            cursor: not-allowed;
            opacity: 0.75;
        }

        .casilla:not(:disabled):hover {
            background: #fbcfe8;
        }

        .boton-unirse {
            background: #db2777;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 25px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            margin: 8px;
        }

        .boton-unirse:hover {
            background: #9d174d;
        }

        .boton-cancelar {
            background: #be123c;
        }

        .boton-nueva {
            background: #831843;
        }

        .mensaje-error {
            color: #b91c1c;
            font-weight: bold;
        }

        .enlace-volver {
            display: inline-block;
            margin-top: 15px;
            color: #9d174d;
        }

        #mensaje-estado {
            color: #9d174d;
            font-weight: bold;
        }

        .compartir-sala {
            margin-top: 25px;
        }

        #mensaje-compartir {
            color: #9d174d;
            font-weight: bold;
        }

        .acciones form {
            margin: 10px auto;
        }

        @media (max-width: 420px) {
            body {
                padding: 15px;
            }

            .tablero {
                grid-template-columns: repeat(3, 85px);
            }

            .tablero form,
            .casilla {
                width: 85px;
                height: 85px;
            }
        }
    </style>
</head>

<body>

    <h1>Ta-Te-Ti Online</h1>

    <div class="informacion">

        <h2>Sala: {{ $partida->codigo_sala }}</h2>

        <p>
            Tu símbolo:
            <strong>{{ $simbolo }}</strong>
        </p>

        <p id="estado-partida">
            Estado: {{ $partida->estado }}
        </p>

        <p id="turno-partida">
            Turno: {{ $partida->turno }}
        </p>

        <p id="mensaje-estado">
            @if ($partida->estado === 'esperando')
                Esperando al segundo jugador...
            @elseif ($partida->estado === 'finalizada')
                ¡Ganó el jugador {{ $partida->ganador }}!
            @elseif ($partida->estado === 'empate')
                ¡La partida terminó en empate!
            @elseif ($partida->estado === 'jugando')
                ¡La partida está en curso!
            @endif
        </p>

    </div>

    <!-- TABLERO DE JUEGO -->

    <div class="tablero">

        @foreach ($partida->tablero as $posicion => $valor)

            <form
                action="{{ route('tateti.jugar', ['codigo' => $partida->codigo_sala]) }}"
                method="POST"
            >
                @csrf

                <input
                    type="hidden"
                    name="posicion"
                    value="{{ $posicion }}"
                >

                <button
                    type="submit"
                    class="casilla"
                    @disabled(
                        $valor !== '' ||
                        $partida->estado !== 'jugando' ||
                        $partida->turno !== $simbolo
                    )
                >
                    {{ $valor }}
                </button>

            </form>

        @endforeach

    </div>

    <div class="acciones">

        <!-- UNIRSE A UNA SALA PRIVADA -->

        @if (
            $partida->tipo === 'privada' &&
            $simbolo === 'ESPECTADOR' &&
            $partida->estado === 'esperando'
        )
            <form
                id="form-unirse"
                action="{{ route('tateti.unirse', ['codigo' => $partida->codigo_sala]) }}"
                method="POST"
            >
                @csrf

                <button type="submit" class="boton-unirse">
                    Unirse como jugador O
                </button>
            </form>
        @endif

        <!-- CANCELAR BÚSQUEDA -->

        @if (
            $partida->tipo === 'publica' &&
            $simbolo === 'X'
        )
            <form
                id="form-cancelar"
                action="{{ route('tateti.cancelar') }}"
                method="POST"
                style="{{ $partida->estado === 'esperando' ? '' : 'display:none;' }}"
            >
                @csrf

                <button
                    type="submit"
                    class="boton-unirse boton-cancelar"
                >
                    Cancelar búsqueda
                </button>
            </form>
        @endif

        <!-- VOLVER A JUGAR EN LA MISMA SALA -->

        @if (in_array($simbolo, ['X', 'O']))

            <form
                id="form-reiniciar"
                action="{{ route('tateti.reiniciar', ['codigo' => $partida->codigo_sala]) }}"
                method="POST"
                style="{{ in_array($partida->estado, ['finalizada', 'empate']) ? '' : 'display:none;' }}"
            >
                @csrf

                <button type="submit" class="boton-unirse">
                    Volver a jugar
                </button>
            </form>

        @endif

        <!-- BUSCAR OTRA PARTIDA -->

        @if (
            $partida->tipo === 'publica' &&
            in_array($simbolo, ['X', 'O'])
        )
            <form
                id="form-otra-partida"
                action="{{ route('tateti.otra-partida') }}"
                method="POST"
                style="{{ in_array($partida->estado, ['finalizada', 'empate']) ? '' : 'display:none;' }}"
            >
                @csrf

                <button
                    type="submit"
                    class="boton-unirse boton-nueva"
                >
                    🔎 Buscar otra partida
                </button>
            </form>
        @endif

    </div>

    @if (session('error'))
        <p class="mensaje-error">
            {{ session('error') }}
        </p>
    @endif

    <!-- COMPARTIR ENLACE SOLO EN SALAS PRIVADAS -->

    @if ($partida->tipo === 'privada')

        <div class="compartir-sala">

            <p>
                Compartí el enlace de la sala con el otro jugador.
            </p>

            <button
                type="button"
                class="boton-unirse"
                onclick="compartirWhatsApp()"
            >
                Compartir por WhatsApp
            </button>

            <button
                type="button"
                class="boton-unirse"
                onclick="copiarEnlace()"
            >
                Copiar enlace
            </button>

            <p id="mensaje-compartir" role="status"></p>

        </div>

    @endif

    <a
        class="enlace-volver"
        href="{{ route('tateti') }}"
    >
        Volver al inicio
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const sala = @json($partida->codigo_sala);
            const miSimbolo = @json($simbolo);

            const botones =
                document.querySelectorAll('.casilla');

            const estadoPartida =
                document.getElementById('estado-partida');

            const turnoPartida =
                document.getElementById('turno-partida');

            const mensajeEstado =
                document.getElementById('mensaje-estado');

            const formularioReiniciar =
                document.getElementById('form-reiniciar');

            const formularioUnirse =
                document.getElementById('form-unirse');

            const formularioCancelar =
                document.getElementById('form-cancelar');

            const formularioOtraPartida =
                document.getElementById('form-otra-partida');

            function actualizarPantalla(evento) {

                // Actualizar las nueve casillas.

                botones.forEach(function (boton, posicion) {

                    const valor =
                        evento.tablero[posicion] ?? '';

                    boton.textContent = valor;

                    const ocupada = valor !== '';

                    const esMiTurno =
                        evento.turno === miSimbolo;

                    const jugando =
                        evento.estado === 'jugando';

                    boton.disabled =
                        ocupada || !esMiTurno || !jugando;
                });

                // Actualizar el estado y el turno.

                estadoPartida.textContent =
                    'Estado: ' + evento.estado;

                turnoPartida.textContent =
                    'Turno: ' + evento.turno;

                // Actualizar los mensajes de la partida.

                if (evento.estado === 'finalizada') {

                    mensajeEstado.textContent =
                        '¡Ganó el jugador ' +
                        evento.ganador + '!';

                } else if (evento.estado === 'empate') {

                    mensajeEstado.textContent =
                        '¡La partida terminó en empate!';

                } else if (evento.estado === 'jugando') {

                    mensajeEstado.textContent =
                        '¡La partida está en curso!';

                } else {

                    mensajeEstado.textContent =
                        'Esperando al segundo jugador...';
                }

                const terminada =
                    evento.estado === 'finalizada' ||
                    evento.estado === 'empate';

                // Mostrar u ocultar Volver a jugar.

                if (formularioReiniciar) {

                    formularioReiniciar.style.display =
                        terminada ? 'block' : 'none';
                }

                // Ocultar Unirse cuando llega el segundo jugador.

                if (
                    formularioUnirse &&
                    evento.estado !== 'esperando'
                ) {

                    formularioUnirse.style.display = 'none';
                }

                // Cancelar búsqueda: solo mientras se espera.

                if (formularioCancelar) {

                    formularioCancelar.style.display =
                        evento.estado === 'esperando'
                            ? 'block'
                            : 'none';
                }

                // Buscar otra partida: solo cuando termina.

                if (formularioOtraPartida) {

                    formularioOtraPartida.style.display =
                        terminada ? 'block' : 'none';
                }
            }

            // Escuchar actualizaciones por WebSockets.

            if (window.Echo) {

                window.Echo.channel('tateti.' + sala)
                    .listen('.TaTeTiActualizado', function (evento) {

                        console.log(
                            'Movimiento recibido por WebSocket:',
                            evento
                        );

                        actualizarPantalla(evento);
                    });

            } else {

                console.error(
                    'Laravel Echo no está disponible. Revisá Vite.'
                );
            }

        });

        // Obtener el enlace actual de la sala.

        function obtenerEnlaceSala() {
            return window.location.href.split('#')[0];
        }

        // Compartir por WhatsApp.

        function compartirWhatsApp() {

            const enlace = obtenerEnlaceSala();

            const mensaje =
                '¡Te invito a jugar Ta-Te-Ti conmigo! 🎮\n' +
                'Entrá a esta sala:\n' + enlace;

            const url =
                'https://wa.me/?text=' +
                encodeURIComponent(mensaje);

            window.open(url, '_blank');
        }

        // Copiar el enlace.

        async function copiarEnlace() {

            const enlace = obtenerEnlaceSala();

            const aviso =
                document.getElementById('mensaje-compartir');

            try {

                await navigator.clipboard.writeText(enlace);

                aviso.textContent = '¡Enlace copiado!';

            } catch (error) {

                aviso.textContent =
                    'No se pudo copiar automáticamente. ' +
                    'Copiá esta dirección: ' + enlace;
            }
        }
    </script>

</body>
</html>
