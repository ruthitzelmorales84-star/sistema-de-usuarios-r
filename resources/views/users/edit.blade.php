<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario</title>
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
            max-width: 640px;
            margin: 0 auto;
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 28px;
        }
        h1 {
            color: #c4b5fd;
            margin-bottom: 8px;
        }
        p.subtitle {
            color: #9ca3af;
            margin-bottom: 22px;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #ddd6fe;
            font-weight: bold;
            font-size: 0.9rem;
        }
        input, select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #3f3b5c;
            background-color: #14131f;
            color: #f3f4f6;
            font-size: 0.95rem;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #8b5cf6;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            background-color: #6f42c1;
            color: #ffffff;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 0.92rem;
        }
        .btn-secondary {
            background-color: #2d2b3e;
            color: #e5e7eb;
        }
        .alert-error {
            padding: 12px 16px;
            border-radius: 8px;
            background-color: #3f1d24;
            color: #fecaca;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar usuario</h1>
        <p class="subtitle">ID #{{ $user->id }} — {{ $user->email }}</p>

        @if ($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            @php
                $selectedRole = old('role', $user->role);
                $roles = ['admin', 'empleado', 'profesor', 'alumno'];
                if ($selectedRole && ! in_array($selectedRole, $roles, true)) {
                    $roles[] = $selectedRole;
                }
            @endphp

            <div class="form-group">
                <label for="role">Rol</label>
                <select id="role" name="role" required>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption }}" @selected($selectedRole === $roleOption)>{{ $roleOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="password">Nueva contraseña (opcional, déjalo vacío si no quieres cambiarla)</label>
                <input type="password" id="password" name="password" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar nueva contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
            </div>

            <div class="actions">
                <button type="submit" class="btn">Guardar cambios</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>