<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partida #{{ $game->id }} - Gato</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #121218;
            color: #f3f4f6;
            font-family: Arial, sans-serif;
            padding: 28px;
        }
        .wrap {
            max-width: 720px;
            margin: 0 auto;
        }
        .card {
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        h1 { color: #c4b5fd; margin-bottom: 10px; }
        .info { margin-bottom: 16px; line-height: 1.6; }
        .board {
            display: grid;
            grid-template-columns: repeat(3, 110px);
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }
        .cell {
            width: 110px;
            height: 110px;
            font-size: 2.4rem;
            font-weight: bold;
            border-radius: 10px;
            border: 2px solid #4c1d95;
            background-color: #14131f;
            color: #f3f4f6;
            cursor: pointer;
        }
        .cell:disabled {
            cursor: not-allowed;
            opacity: 0.85;
        }
        .btn {
            display: inline-block;
            padding: 9px 14px;
            border-radius: 8px;
            border: none;
            background-color: #6f42c1;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-red { background-color: #b91c1c; }
        .btn-gray { background-color: #2d2b3e; color: #e5e7eb; }
        .alert-err {
            background-color: #450a0a;
            color: #fecaca;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
            <h1>Partida #{{ $game->id }}</h1>
            <a href="{{ route('multiplayer.index') }}" class="btn btn-gray">Volver al lobby</a>
        </div>

        <div class="info">
            <p><strong>Jugador X:</strong> {{ $game->playerX->name }}</p>
            <p><strong>Jugador O:</strong> <span id="player-o-name">{{ $game->playerO?->name ?? 'Esperando rival...' }}</span></p>
            <p><strong>Estado:</strong> <span id="game-status">{{ $game->status }}</span></p>
            <p><strong>Turno actual:</strong> <span id="turn-name">{{ $game->currentTurnUser?->name ?? '-' }}</span></p>
            <p><strong>Ganador:</strong> <span id="winner-name">{{ $game->winner?->name ?? '-' }}</span></p>
        </div>

        @if ($errors->any())
            <div class="alert-err">{{ $errors->first() }}</div>
        @endif

        <form id="move-form" action="{{ route('multiplayer.move', $game) }}" method="POST">
            @csrf
            <input type="hidden" name="position" id="position-input" value="">
            <div class="board" id="board">
                @for ($i = 0; $i < 9; $i++)
                    @php
                        $val = $game->board[$i] ?? null;
                        $isMyTurn = $game->status === 'playing' && (int) $game->current_turn_user_id === (int) $user->id;
                        $disabled = ! $isMyTurn || $val !== null;
                    @endphp
                    <button type="button" class="cell" data-pos="{{ $i }}" @disabled($disabled)>
                        {{ $val ?? '' }}
                    </button>
                @endfor
            </div>
        </form>

        @if ($isParticipant && in_array($game->status, ['waiting', 'playing'], true))
            <form action="{{ route('multiplayer.abandon', $game) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-red">Abandonar partida</button>
            </form>
        @endif
    </div>
</div>

<script>
    const currentUserId = {{ (int) $user->id }};
    const moveForm = document.getElementById('move-form');
    const posInput = document.getElementById('position-input');
    const cells = document.querySelectorAll('.cell');

    cells.forEach(cell => {
        cell.addEventListener('click', () => {
            if (cell.disabled) return;
            posInput.value = cell.dataset.pos;
            moveForm.submit();
        });
    });

    function refreshBoard() {
        fetch("{{ route('multiplayer.state', $game) }}")
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (!data) return;
                document.getElementById('player-o-name').textContent = data.player_o_name ?? 'Esperando rival...';
                document.getElementById('game-status').textContent = data.status;
                document.getElementById('turn-name').textContent = data.current_turn_name ?? '-';
                document.getElementById('winner-name').textContent = data.winner_name ?? '-';

                const isMyTurn = data.status === 'playing' && Number(data.current_turn_user_id) === currentUserId;

                cells.forEach(cell => {
                    const idx = Number(cell.dataset.pos);
                    const val = data.board[idx] ?? '';
                    cell.textContent = val;
                    cell.disabled = !isMyTurn || val !== '';
                });
            })
            .catch(() => {});
    }

    setInterval(refreshBoard, 2000);
</script>
</body>
</html>
