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
            max-width: 520px;
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
        <h1>Panel Principal</h1>
        <p class="subtitle">Selecciona el módulo al que deseas entrar</p>

        <div class="actions">
            <a href="{{ url('/sistema-usuarios') }}" class="btn-module">Sistema de usuarios</a>
            <a href="{{ url('/sistema-administracion') }}" class="btn-module btn-secondary">Sistema de administración</a>
            <a href="{{ route('products.index') }}" class="btn-module btn-secondary">Sistema de gestión de productos</a>
            <a href="{{ url('/juego-multijugador') }}" class="btn-module btn-secondary">Juego multijugador</a>
        </div>
    </main>
</body>
</html>