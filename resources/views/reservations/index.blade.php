<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservación de equipos</title>
    <style>
        body { margin: 0; padding: 24px; background: #111827; color: #f9fafb; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: auto; }
        table { width: 100%; border-collapse: collapse; background: #1f2937; }
        th, td { padding: 10px; border-bottom: 1px solid #374151; text-align: left; }
        form.inline { display: inline; }
        input, textarea, button { padding: 8px; margin: 4px 0; }
        .card { background: #1f2937; padding: 16px; margin: 16px 0; border-radius: 8px; }
        .success { color: #86efac; } .error { color: #fca5a5; }
    </style>
</head>
<body>
<main>
    <h1>Reservación de equipos de cómputo</h1>
    <p>Usuario: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role }})</p>
    @if(session('success')) <p class="success">{{ session('success') }}</p> @endif
    @if($errors->any()) <p class="error">{{ $errors->first() }}</p> @endif

    @if($isAdmin)
        <section class="card">
            <h2>Registrar equipo</h2>
            <form method="POST" action="{{ route('reservations.equipment.store') }}">
                @csrf
                <input name="name" placeholder="Nombre del equipo" required>
                <input name="location" placeholder="Ubicación">
                <textarea name="description" placeholder="Descripción"></textarea>
                <button type="submit">Agregar</button>
            </form>
        </section>
    @endif

    <section class="card">
        <h2>Equipos</h2>
        <table>
            <thead><tr><th>Equipo</th><th>Ubicación</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @foreach($computers as $computer)
                <tr>
                    <td>{{ $computer->name }}</td>
                    <td>{{ $computer->location ?? '-' }}</td>
                    <td>{{ $computer->description ?? '-' }}</td>
                    <td>{{ $computer->status === 'available' ? 'Disponible' : 'Reservado' }}</td>
                    <td>
                        @if($computer->status === 'available' && in_array(auth()->user()->role, ['estudiante', 'alumno'], true))
                            <form class="inline" method="POST" action="{{ route('reservations.reserve', $computer) }}">
                                @csrf <button type="submit">Reservar</button>
                            </form>
                        @endif
                        @if($isAdmin)
                            <form class="inline" method="POST" action="{{ route('reservations.equipment.destroy', $computer) }}" onsubmit="return confirm('¿Eliminar equipo?')">
                                @csrf @method('DELETE') <button type="submit">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>{{ $isAdmin ? 'Todas las reservaciones' : 'Mis reservaciones' }}</h2>
        <table>
            <thead><tr><th>Equipo</th>@if($isAdmin)<th>Usuario</th>@endif<th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @foreach($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->computer->name }}</td>
                    @if($isAdmin)<td>{{ $reservation->user->name }}</td>@endif
                    <td>{{ $reservation->reserved_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $reservation->cancelled_at ? 'Cancelada' : 'Activa' }}</td>
                    <td>
                        @if(!$reservation->cancelled_at)
                            <form class="inline" method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                                @csrf <button type="submit">Cancelar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
    <a href="{{ route('home') }}">Volver al inicio</a>
</main>
</body>
</html>
