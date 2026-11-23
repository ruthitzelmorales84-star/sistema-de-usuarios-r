<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de usuarios</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121218;
            color: #f3f4f6;
            padding: 32px;
        }
        .container {
            max-width: 1020px;
            margin: 0 auto;
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 24px;
        }
        h1, h2 {
            margin-top: 0;
            color: #c4b5fd;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .actions-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            background-color: #6f42c1;
            color: #ffffff;
            font-weight: bold;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
        }
        .btn-secondary {
            background-color: #2d2b3e;
            color: #e5e7eb;
        }
        .btn-edit {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .btn-delete {
            background-color: #dc2626;
            color: #ffffff;
        }
        .btn-restore {
            background-color: #059669;
            color: #ffffff;
        }
        .mode-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.85rem;
            margin-bottom: 16px;
            background-color: #28243b;
            color: #ddd6fe;
            border: 1px solid #4c1d95;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            margin-bottom: 28px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #2e2c42;
        }
        th {
            color: #a78bfa;
            background-color: #161522;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            background-color: #1e3a2f;
            color: #a7f3d0;
            margin-bottom: 16px;
        }
        .alert-error {
            padding: 12px 16px;
            border-radius: 8px;
            background-color: #3f1d24;
            color: #fecaca;
            margin-bottom: 16px;
        }
        .empty-msg {
            color: #9ca3af;
            margin-bottom: 24px;
        }
        .badge-action {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: bold;
            background-color: #2e2646;
            color: #ddd6fe;
        }
    </style>
</head>
<body>
    @php
        $totalEliminados = isset($trashedUsers) ? $trashedUsers->count() : 0;
        $totalMovimientos = is_array($movements ?? null) ? count($movements) : (isset($movements) ? $movements->count() : 0);
    @endphp

    <div class="container">
        <div class="top-bar">
            <h1>Lista de usuarios</h1>
            <div class="actions-group">
                @if(in_array(auth()->user()?->role, ['admin', 'empleado'], true))
                    <a href="{{ route('users.create') }}" class="btn">Nuevo usuario</a>
                @endif
                @if(auth()->user()?->role === 'admin')
                    <button type="button" id="toggleHistorialBtn" onclick="toggleHistorialSection()" class="btn btn-secondary">
                        Historial ({{ $totalEliminados }} / {{ $totalMovimientos }} mov.)
                    </button>
                @endif
                <a href="{{ route('modules.users') }}" class="btn btn-secondary">Módulo de usuarios</a>
                <a href="{{ route('home') }}" class="btn">Volver al inicio</a>
            </div>
        </div>

        <div class="mode-badge">
            @if(auth()->user()?->role === 'admin')
                Modo Administrador (acceso completo: editar, eliminar e historial)
            @elseif(auth()->user()?->role === 'empleado')
                Modo Empleado (puedes consultar, registrar y editar; no puedes eliminar)
            @else
                Modo Lectura: solo puedes consultar usuarios
            @endif
        </div>

        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    @if(in_array(auth()->user()?->role, ['admin', 'empleado'], true))
                        <th>Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($users ?? collect() as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role ?? 'usuario' }}</td>
                        @if(in_array(auth()->user()?->role, ['admin', 'empleado'], true))
                            <td>
                                <div class="actions-group">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-edit">Editar</a>
                                    @if(auth()->user()?->role === 'admin')
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar/deshabilitar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-delete">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ in_array(auth()->user()?->role, ['admin', 'empleado'], true) ? 5 : 4 }}">No hay usuarios registrados todavía.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(auth()->user()?->role === 'admin')
        <div id="historialSection" style="display: none;">
            <h2>Historial / Usuarios deshabilitados</h2>
            @if (isset($trashedUsers) && $trashedUsers->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Eliminado el</th>
                            @if(auth()->user()?->role === 'admin')
                                <th>Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trashedUsers as $trashed)
                            <tr>
                                <td>{{ $trashed->id }}</td>
                                <td>{{ $trashed->name }}</td>
                                <td>{{ $trashed->email }}</td>
                                <td>{{ $trashed->role ?? 'usuario' }}</td>
                                <td>{{ $trashed->deleted_at }}</td>
                                @if(auth()->user()?->role === 'admin')
                                    <td>
                                        <div class="actions-group">
                                            <form action="{{ route('users.restore', $trashed->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-restore">Restaurar</button>
                                            </form>

                                            <form action="{{ route('users.force-destroy', $trashed->id) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente de la base de datos?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-delete">Eliminar definitivo</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-msg">No hay registros en el historial de usuarios eliminados.</p>
            @endif

            <h2>Movimientos recientes</h2>
            @if (!empty($movements) && (is_array($movements) ? count($movements) > 0 : $movements->count() > 0))
                <table>
                    <thead>
                        <tr>
                            <th>Acción</th>
                            <th>Detalle</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $item)
                            @php
                                $action = is_array($item) ? ($item['action'] ?? 'Movimiento') : ($item->action ?? 'Movimiento');
                                $description = is_array($item) ? ($item['description'] ?? '') : ($item->description ?? '');
                                $actor = is_array($item)
                                    ? ($item['actor'] ?? ($item['actor_name'] ?? 'Sistema'))
                                    : ($item->actor_name ?? ($item->actor ?? 'Sistema'));
                                $createdAt = is_array($item) ? ($item['created_at'] ?? '') : ($item->created_at ?? '');
                            @endphp
                            <tr>
                                <td><span class="badge-action">{{ $action }}</span></td>
                                <td>{{ $description }}</td>
                                <td>{{ $actor }}</td>
                                <td>{{ $createdAt }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-msg">No hay movimientos registrados todavía.</p>
            @endif
        </div>
        @endif
    </div>

    <script>
        function toggleHistorialSection() {
            const section = document.getElementById('historialSection');
            if (!section) return;
            const currentlyHidden = section.style.display === 'none' || getComputedStyle(section).display === 'none';
            section.style.display = currentlyHidden ? 'block' : 'none';
        }
    </script>
</body>
</html>