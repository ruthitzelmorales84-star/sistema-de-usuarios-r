<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at top, #1d1433 0%, #0d0e17 65%);
            color: #f3f4f6;
        }

        .login-card {
            width: 100%;
            max-width: 410px;
            background-color: #161826;
            border: 1px solid rgba(139, 92, 246, 0.25);
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
        }

        .login-header {
            margin-bottom: 24px;
            text-align: center;
        }

        .login-badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #c4b5fd;
            background-color: rgba(111, 66, 193, 0.22);
            border: 1px solid rgba(139, 92, 246, 0.35);
            padding: 5px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 14px;
            color: #9ca3af;
        }

        .alert-error {
            background-color: rgba(220, 38, 38, 0.16);
            border: 1px solid rgba(248, 113, 113, 0.45);
            color: #fca5a5;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 8px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 15px;
            color: #ffffff;
            background-color: #0e101b;
            border: 1px solid #2b2f45;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.28);
        }

        .field-error {
            display: block;
            color: #f87171;
            font-size: 12.5px;
            margin-top: 6px;
        }

        .btn-submit {
            width: 100%;
            padding: 13px 18px;
            margin-top: 6px;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #6f42c1 0%, #5b21b6 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 24px rgba(111, 66, 193, 0.35);
        }

        .btn-submit:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13.5px;
            color: #93c5fd;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
    @include('components.minimal-theme')
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <span class="login-badge">Acceso al sistema</span>
            <h1>Iniciar sesión</h1>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="correo@ejemplo.com"
                    required
                    autofocus
                >
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    required
                >
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-submit">Entrar</button>
        </form>

        <a href="{{ route('home') }}" class="back-link">Volver al panel principal</a>
    </div>
</body>
</html>