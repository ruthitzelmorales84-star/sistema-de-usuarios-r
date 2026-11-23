<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar usuario</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121218;
            color: #f3f4f6;
            padding: 32px;
        }
        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 24px;
        }
        h1 {
            margin-top: 0;
            color: #c4b5fd;
        }
        .field {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #d1d5db;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #3b3759;
            background-color: #121218;
            color: #f9fafb;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            background-color: #6f42c1;
            color: #ffffff;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-secondary {
            background-color: #2d2b3e;
            color: #e5e7eb;
        }
        .error-box {
            background-color: #3b1822;
            border: 1px solid #88253f;
            color: #fecdd3;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
    </style>
    @include('components.minimal-theme')
</head>
<body>
    <div class="container">
        <h1>Agregar usuario</h1>

        @if ($errors->any())
            <div class="error-box">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="field">
                <label for="name">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label for="role">Rol</label>
                <select id="role" name="role" required>
                    @foreach (auth()->user()->role === 'admin'
                        ? ['admin', 'empleado', 'profesor', 'estudiante', 'jugador']
                        : ['empleado', 'profesor', 'estudiante', 'jugador'] as $role)
                        <option value="{{ $role }}" @selected(old('role', 'empleado') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Guardar usuario</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Ver lista</a>
            </div>
        </form>
    </div>
</body>
</html>