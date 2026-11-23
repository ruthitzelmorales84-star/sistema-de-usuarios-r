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
        .section-nav { display: flex; flex-wrap: wrap; gap: 8px; margin: 20px 0; }
        .section-link { display: inline-block; padding: 10px 14px; border: 1px solid #374151; border-radius: 6px; color: #f9fafb; text-decoration: none; }
        .section-link.active { background: #374151; }
    </style>
    @include('components.minimal-theme')
</head>
<body>
<main>
    <h1>Reservación de equipos de cómputo</h1>
    <p>Usuario: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role }})</p>
    <nav class="section-nav" aria-label="Secciones de reservaciones">
        <a class="section-link {{ $section === 'reserve' ? 'active' : '' }}" href="{{ route('reservations.index', ['section' => 'reserve']) }}">Reservar</a>
        <a class="section-link {{ $section === 'list' ? 'active' : '' }}" href="{{ route('reservations.index', ['section' => 'list']) }}">Ver reservaciones</a>
    </nav>
    @if(session('success')) <p class="success">{{ session('success') }}</p> @endif
    @if($errors->any()) <p class="error">{{ $errors->first() }}</p> @endif

    @if($section === 'reserve')
        @if($isAdmin)
            <section class="card">
                <h2>Registrar equipo</h2>
                <form method="POST" action="{{ route('reservations.equipment.store') }}">
                    @csrf
                    <input name="name" placeholder="Nombre del equipo" required>
                    <textarea name="description" placeholder="Descripción"></textarea>
                    <button type="submit">Agregar</button>
                </form>
            </section>
        @endif

        <section class="card">
            <h2>Equipos disponibles</h2>
            <table>
                <thead><tr><th>Equipo</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse($computers as $computer)
                    <tr>
                        <td>{{ $computer->name }}</td>
                        <td>{{ $computer->description ?? '-' }}</td>
                        <td>{{ $computer->status === 'available' ? 'Disponible' : 'Reservado' }}</td>
                        <td>
                            @if($computer->status === 'available' && auth()->user()->role === 'estudiante')
                                <form class="inline" method="POST" action="{{ route('reservations.reserve', $computer) }}" onsubmit="return confirm('¿Confirmas la reservación de este equipo?')">
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
                @empty
                    <tr><td colspan="4">No hay equipos registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    @else
        <section class="card">
            <h2>{{ $isAdmin ? 'Todas las reservaciones' : 'Mis reservaciones' }}</h2>
            <table>
                <thead><tr><th>Equipo</th><th>Reservado por</th><th>Fecha y hora de reservación</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->computer->name }}</td>
                        <td>{{ $reservation->user->name }}</td>
                        <td>{{ $reservation->reserved_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $reservation->cancelled_at ? 'Cancelada' : 'Activa' }}</td>
                        <td>
                            @if(!$reservation->cancelled_at)
                                <form class="inline" method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                                    @csrf <button type="submit">Cancelar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aún no hay reservaciones.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>
    @endif
    <a href="{{ route('home') }}">Volver al inicio</a>
</main>
</body>
</html>
