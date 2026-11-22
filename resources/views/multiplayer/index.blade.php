<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego del Gato Multijugador</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #121218;
            color: #f3f4f6;
            font-family: Arial, sans-serif;
            padding: 28px;
        }
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        h1 { color: #c4b5fd; font-size: 1.7rem; }
        h2 { color: #ddd6fe; font-size: 1.15rem; margin-bottom: 12px; }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .card {
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            background-color: #6f42c1;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-green { background-color: #15803d; }
        .btn-red { background-color: #b91c1c; }
        .btn-gray { background-color: #2d2b3e; color: #e5e7eb; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #2d2b3e;
            text-align: left;
            font-size: 0.9rem;
        }
        input, select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #3f3b5c;
            background-color: #14131f;
            color: #f3f4f6;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        .alert-ok {
            background-color: #14532d;
            color: #bbf7d0;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .alert-err {
            background-color: #450a0a;
            color: #fecaca;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        ul { list-style: none; }
        li { padding: 8px 0; border-bottom: 1px solid #2d2b3e; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top-bar">
        <div>
            <h1>Juego del Gato Multijugador</h1>
            <p>Usuario: <strong>{{ $user->name }}</strong> ({{ $user->role }})</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <form action="{{ route('multiplayer.create') }}" method="POST">
                @csrf
                <button type="submit" class="btn">Crear nueva partida</button>
            </form>
            <a href="{{ route('home') }}" class="btn btn-gray">Inicio</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-red">Cerrar sesión</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-ok">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-err">{{ $errors->first() }}</div>
    @endif

    <div class="grid-2">
        <div class="card">
            <h2>Partidas disponibles para unirse</h2>
            <ul>
                @forelse ($availableGames as $g)
                    <li>
                        <span>Partida #{{ $g->id }} — Creada por {{ $g->playerX->name }} (X)</span>
                        <form action="{{ route('multiplayer.join', $g) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-green">Unirse como O</button>
                        </form>
                    </li>
                @empty
                    <li>No hay partidas disponibles por ahora.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <h2>Jugadores disponibles</h2>
            <ul>
                @forelse ($availablePlayers as $p)
                    <li>
                        <span>{{ $p->name }} ({{ $p->email }})</span>
                        <span>{{ $p->role }}</span>
                    </li>
                @empty
                    <li>No hay otros jugadores registrados.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="card">
        <h2>Partidas en curso o en espera</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Jugador X</th>
                    <th>Jugador O</th>
                    <th>Turno actual</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activeGames as $g)
                    <tr>
                        <td>#{{ $g->id }}</td>
                        <td>{{ $g->playerX->name }}</td>
                        <td>{{ $g->playerO?->name ?? 'Esperando rival' }}</td>
                        <td>{{ $g->currentTurnUser?->name ?? '-' }}</td>
                        <td>{{ $g->status }}</td>
                        <td style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="{{ route('multiplayer.show', $g) }}" class="btn">Entrar al tablero</a>
                            @if (in_array($user->id, [$g->player_x_id, $g->player_o_id], true))
                                <form action="{{ route('multiplayer.abandon', $g) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-red">Abandonar</button>
                                </form>
                            @endif
                            @if ($isAdmin)
                                <form action="{{ route('multiplayer.cancel', $g) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-red">Cancelar partida</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay partidas activas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>{{ $isAdmin ? 'Historial general de partidas' : 'Mi historial de partidas' }}</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Jugador X</th>
                    <th>Jugador O</th>
                    <th>Resultado</th>
                    <th>Ganador</th>
                    <th>Ver</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historyGames as $g)
                    <tr>
                        <td>#{{ $g->id }}</td>
                        <td>{{ $g->playerX->name }}</td>
                        <td>{{ $g->playerO?->name ?? '-' }}</td>
                        <td>{{ $g->status }}</td>
                        <td>{{ $g->winner?->name ?? 'Empate / Sin ganador' }}</td>
                        <td><a href="{{ route('multiplayer.show', $g) }}" class="btn btn-gray">Tablero</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Aún no hay partidas finalizadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($isAdmin)
        <div class="card">
            <h2>Administración: Todas las partidas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jugador X</th>
                        <th>Jugador O</th>
                        <th>Estado</th>
                        <th>Ganador</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allGames as $g)
                        <tr>
                            <td>#{{ $g->id }}</td>
                            <td>{{ $g->playerX->name }}</td>
                            <td>{{ $g->playerO?->name ?? '-' }}</td>
                            <td>{{ $g->status }}</td>
                            <td>{{ $g->winner?->name ?? '-' }}</td>
                            <td>
                                @if (in_array($g->status, ['waiting', 'playing'], true))
                                    <form action="{{ route('multiplayer.cancel', $g) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-red">Cancelar</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Administración: Usuarios</h2>
            <form action="{{ route('multiplayer.users.store') }}" method="POST" style="margin-bottom: 16px;">
                @csrf
                <input type="text" name="name" placeholder="Nombre" required>
                <input type="email" name="email" placeholder="Correo" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <select name="role" required>
                    <option value="jugador">jugador</option>
                    <option value="admin">admin</option>
                </select>
                <button type="submit" class="btn btn-green">Crear usuario</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allUsers as $u)
                        <tr>
                            <td>#{{ $u->id }}</td>
                            <td>
                                <form action="{{ route('multiplayer.users.update', $u) }}" method="POST" style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $u->name }}" required>
                                    <input type="email" name="email" value="{{ $u->email }}" required>
                                    <select name="role">
                                        <option value="jugador" @selected($u->role === 'jugador')>jugador</option>
                                        <option value="admin" @selected($u->role === 'admin')>admin</option>
                                    </select>
                                    <button type="submit" class="btn">Guardar</button>
                                </form>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->role }}</td>
                            <td>
                                @if ((int) $u->id !== (int) $user->id)
                                    <form action="{{ route('multiplayer.users.destroy', $u) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-red">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
</body>
</html>