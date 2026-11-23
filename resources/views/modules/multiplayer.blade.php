<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego multijugador</title>
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
            padding: 13px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #7e22ce 0%, #581c87 100%);
            border: 1px solid rgba(192, 132, 252, 0.45);
        }
        .btn-back {
            background: rgba(126, 34, 206, 0.18);
        }
    </style>
    @include('components.minimal-theme')
</head>
<body>
    <main class="card">
        <h1>Juego multijugador</h1>
        <p>Espacio listo para iniciar partidas y salas multijugador.</p>
        <div class="actions">
            <a href="{{ route('home') }}" class="btn btn-back">Volver al inicio</a>
        </div>
    </main>
</body>
</html>