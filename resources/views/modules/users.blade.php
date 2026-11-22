<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de usuarios</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
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
        .card {
            width: 100%;
            max-width: 560px;
            background: rgba(24, 17, 38, 0.92);
            border: 1px solid rgba(168, 85, 247, 0.35);
            border-radius: 20px;
            padding: 36px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
            text-align: center;
        }
        h1 { font-size: 1.7rem; margin-bottom: 10px; }
        p { color: #b8a9d6; margin-bottom: 24px; }
        .actions { display: flex; flex-direction: column; gap: 12px; }
        .btn {
            display: inline-block;
            width: 100%;
            padding: 13px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            color: #ffffff;
            background: linear-gradient(135deg, #7e22ce 0%, #581c87 100%);
            border: 1px solid rgba(192, 132, 252, 0.45);
            cursor: pointer;
        }
        .btn-back {
            background: rgba(126, 34, 206, 0.18);
        }
        .btn-logout {
            background: rgba(220, 38, 38, 0.22);
            border-color: rgba(248, 113, 113, 0.45);
            color: #fecaca;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Sistema de usuarios</h1>
        <p>
            Sesión activa: <strong>{{ auth()->user()->email }}</strong>
            ({{ auth()->user()->role === 'admin' ? 'Administrador' : 'Consulta' }})
        </p>
        <div class="actions">
            <a href="{{ route('users.index') }}" class="btn">Ver lista de usuarios</a>

            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('users.create') }}" class="btn">Agregar usuario</a>
            @endif

            <a href="{{ route('home') }}" class="btn btn-back">Volver al inicio</a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-logout">Cerrar sesión / Cambiar de cuenta</button>
            </form>
        </div>
    </main>
</body>
</html>