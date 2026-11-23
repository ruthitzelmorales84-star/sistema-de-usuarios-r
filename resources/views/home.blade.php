<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #24143d 0%, #0d0b14 65%);
            color: #f4f1fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .panel {
            width: 100%;
            max-width: 620px;
            background: rgba(24, 17, 38, 0.92);
            border: 1px solid rgba(168, 85, 247, 0.35);
            border-radius: 20px;
            padding: 36px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
            text-align: center;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffffff;
        }

        .subtitle {
            color: #b8a9d6;
            font-size: 0.95rem;
            margin-bottom: 28px;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .login-form {
            max-width: 410px;
            margin: 0 auto;
            text-align: left;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #e5e7eb;
            font-size: 0.9rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #3f3b5c;
            border-radius: 10px;
            background: #14131f;
            color: #f3f4f6;
            font-size: 1rem;
        }

        .btn-module {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 15px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #7e22ce 0%, #581c87 100%);
            border: 1px solid rgba(192, 132, 252, 0.45);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .btn-module:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #9333ea 0%, #6b21a8 100%);
            box-shadow: 0 10px 24px rgba(147, 51, 234, 0.35);
        }

        .btn-submit,
        .btn-logout {
            width: 100%;
            padding: 13px 18px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, #6f42c1 0%, #5b21b6 100%);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-logout {
            background: rgba(220, 38, 38, 0.22);
            border: 1px solid rgba(248, 113, 113, 0.45);
            color: #fecaca;
        }

        .alert-error {
            margin: 0 auto 18px;
            max-width: 410px;
            padding: 12px 14px;
            border: 1px solid rgba(248, 113, 113, 0.45);
            border-radius: 10px;
            background: rgba(220, 38, 38, 0.16);
            color: #fca5a5;
            text-align: left;
        }

        .btn-secondary {
            background: rgba(126, 34, 206, 0.18);
            border: 1px solid rgba(192, 132, 252, 0.35);
        }

        .btn-secondary:hover {
            background: rgba(147, 51, 234, 0.32);
        }
    </style>
</head>
<body>
    <main class="panel">
        @auth
            @php($currentUser = auth()->user())
            <h1>Panel Principal</h1>
            <p class="subtitle">Hola, {{ $currentUser->name }}. Selecciona un módulo disponible para tu rol ({{ $currentUser->role }}).</p>

            <div class="actions">
                @if($currentUser->role === 'admin')
                    <a href="{{ route('modules.users') }}" class="btn-module">Sistema de usuarios</a>
                    <a href="{{ route('modules.academic') }}" class="btn-module btn-secondary">Sistema académico</a>
                    <a href="{{ route('modules.admin') }}" class="btn-module btn-secondary">Sistema de administración</a>
                    <a href="{{ route('products.index') }}" class="btn-module btn-secondary">Sistema de gestión de productos</a>
                    <a href="{{ route('reservations.index') }}" class="btn-module btn-secondary">Reservación de equipos</a>
                    <a href="{{ route('multiplayer.index') }}" class="btn-module btn-secondary">Juego multijugador</a>
                @elseif($currentUser->role === 'empleado')
                    <a href="{{ route('modules.users') }}" class="btn-module">Sistema de usuarios</a>
                    <a href="{{ route('products.index') }}" class="btn-module btn-secondary">Sistema de gestión de productos</a>
                @elseif($currentUser->role === 'profesor')
                    <a href="{{ route('modules.academic') }}" class="btn-module">Sistema académico</a>
                @elseif($currentUser->role === 'estudiante')
                    <a href="{{ route('modules.academic') }}" class="btn-module">Sistema académico</a>
                    <a href="{{ route('reservations.index') }}" class="btn-module btn-secondary">Reservación de equipos</a>
                @elseif($currentUser->role === 'jugador')
                    <a href="{{ route('multiplayer.index') }}" class="btn-module">Juego multijugador</a>
                @else
                    <p class="subtitle">Tu cuenta aún no tiene módulos asignados.</p>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">Cerrar sesión</button>
                </form>
            </div>
        @else
            <h1>Iniciar sesión</h1>
            <p class="subtitle">Ingresa tus credenciales para acceder a tus módulos</p>

            @if($errors->any())
                <div class="alert-error" role="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="correo@ejemplo.com"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>
                <button type="submit" class="btn-submit">Entrar</button>
            </form>
        @endauth
    </main>
</body>
</html>