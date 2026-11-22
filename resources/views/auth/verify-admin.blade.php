<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en dos pasos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d0b14;
            color: #f3f4f6;
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #171522;
            border: 1px solid #2b273d;
            border-radius: 16px;
            padding: 32px;
        }
        .badge {
            display: inline-block;
            background: rgba(111, 66, 193, 0.22);
            color: #c4b5fd;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 14px;
        }
        h1 { font-size: 26px; margin-bottom: 8px; }
        p.sub { color: #9ca3af; font-size: 14px; margin-bottom: 20px; line-height: 1.4; }
        .alert-status {
            background: rgba(111, 66, 193, 0.16);
            border: 1px solid rgba(139, 92, 246, 0.45);
            color: #ddd6fe;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-error {
            background: rgba(185, 28, 28, 0.2);
            border: 1px solid rgba(248, 113, 113, 0.45);
            color: #fca5a5;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        label { display: block; font-size: 14px; font-weight: bold; margin-bottom: 8px; }
        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #342f49;
            background: #0f0d17;
            color: #fff;
            font-size: 20px;
            letter-spacing: 6px;
            text-align: center;
            margin-bottom: 18px;
        }
        input:focus { outline: none; border-color: #6f42c1; }
        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #6f42c1;
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover { background: #5b32a3; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #93c5fd;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">VERIFICACIÓN 2FA</span>
        <h1>Código de seguridad</h1>
        <p class="sub">Ingresa el código de 6 dígitos que se generó para completar tu acceso de administrador.</p>

        @if (session('status'))
            <div class="alert-status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.2fa.verify') }}">
            @csrf
            <label for="code">Código de 6 dígitos</label>
            <input type="text" id="code" name="code" maxlength="6" autocomplete="one-time-code" required autofocus>
            <button type="submit">Verificar código</button>
        </form>

        <a class="back-link" href="{{ route('login') }}">Volver al inicio de sesión</a>
    </div>
</body>
</html>