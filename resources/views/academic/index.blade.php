<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Académico</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background-color: #121218;
            color: #f3f4f6;
            font-family: Arial, sans-serif;
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
        h2 {
            color: #c4b5fd;
            margin-bottom: 12px;
        }
        a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .top-meta {
            margin-bottom: 14px;
            line-height: 1.6;
        }
        .btn-sm {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            background-color: #6f42c1;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-danger {
            background-color: #dc2626;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        .sort-bar {
            margin-top: 14px;
            padding: 10px 12px;
            background-color: #151420;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        th, td {
            padding: 10px 12px;
            border: 1px solid #2e2c42;
            text-align: left;
        }
        th {
            background-color: #161522;
            color: #c4b5fd;
        }
    </style>
    @include('components.minimal-theme')
</head>
<body>
    <div class="container">
        @php
            $currentUser = auth()->user();
            $currentName = $currentUser?->name ?? 'Invitado';

            $sessionRole = session('academic_access_role');
            $profVerified = (bool) session('academic_2fa_verified', false);

            if ($sessionRole === 'profesor' && $profVerified) {
                $currentRole = 'profesor';
            } elseif ($sessionRole && $sessionRole !== 'profesor') {
                $currentRole = $sessionRole;
            } else {
                $currentRole = $currentUser?->role ?? 'estudiante';
            }
        @endphp

        <h2>Sistema Académico</h2>

        <p class="top-meta">
            Sesión: <strong>{{ $currentName }}</strong> ({{ $currentRole }})
            | <a href="{{ route('modules.academic') }}">Cambiar rol</a>
            @if ($currentRole === 'profesor')
                | <a href="{{ route('academic.create') }}">+ Registrar alumno</a>
            @endif
        </p>

        @if ($currentUser)
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-sm">Cerrar sesión</button>
            </form>
        @endif

        @if (session('success'))
            <p style="color: #6ee7b7; margin-top: 12px;">{{ session('success') }}</p>
        @endif

        <div class="sort-bar">
            <strong>Ordenar por:</strong>
            <a href="{{ route('academic.index', ['sort' => 'student_name', 'direction' => ($sort === 'student_name' && $direction === 'asc') ? 'desc' : 'asc']) }}">
                Nombre ({{ $sort === 'student_name' ? strtoupper($direction) : 'A-Z' }})
            </a> |
            <a href="{{ route('academic.index', ['sort' => 'grade', 'direction' => ($sort === 'grade' && $direction === 'desc') ? 'asc' : 'desc']) }}">
                Calificación ({{ $sort === 'grade' ? ($direction === 'desc' ? 'Mayor a menor' : 'Menor a mayor') : 'Mayor-Menor' }})
            </a> |
            <a href="{{ route('academic.index', ['sort' => 'absences', 'direction' => ($sort === 'absences' && $direction === 'desc') ? 'asc' : 'desc']) }}">
                Faltas ({{ $sort === 'absences' ? ($direction === 'desc' ? 'Mayor a menor' : 'Menor a mayor') : 'Mayor-Menor' }})
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre del alumno</th>
                    <th>Número de faltas</th>
                    <th>Calificación numérica</th>
                    <th>Equivalencia</th>
                    @if ($currentRole === 'profesor')
                        <th>Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->student_name }}</td>
                        <td>{{ $record->absences }}</td>
                        <td>{{ number_format((float) $record->grade, 1) }}</td>
                        <td><strong>{{ \App\Models\AcademicRecord::letterGradeLabel($record->letter_grade ?? $record->equivalence) }}</strong></td>
                        @if ($currentRole === 'profesor')
                            <td>
                                <a href="{{ route('academic.edit', $record) }}">Editar</a> |
                                <form method="POST" action="{{ route('academic.destroy', $record) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" onclick="return confirm('¿Eliminar este registro académico?')">Eliminar</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $currentRole === 'profesor' ? 5 : 4 }}">No hay registros académicos disponibles.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>