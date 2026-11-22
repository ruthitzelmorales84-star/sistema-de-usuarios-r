<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema Académico</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background-color: #121218;
            color: #f3f4f6;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
        }
        h1 { color: #c4b5fd; margin-bottom: 10px; font-size: 1.5rem; }
        p { color: #cbd5e1; margin-bottom: 22px; }
        .options { display: flex; flex-direction: column; gap: 12px; }
        .btn {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-prof { background-color: #6f42c1; color: #ffffff; }
        .btn-alum { background-color: #3730a3; color: #ffffff; }
        .btn-back { background-color: #2d2b3e; color: #e5e7eb; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Sistema académico</h1>
        <p>¿Cómo deseas ingresar?</p>

        <div class="options">
            <a href="{{ route('academic.professor.form') }}" class="btn btn-prof">Soy Profesor (verificar código por correo)</a>

            <form action="{{ route('academic.student.enter') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-alum">Soy Alumno (solo consulta)</button>
            </form>

            <a href="{{ route('home') }}" class="btn btn-back">Volver al inicio</a>
        </div>
    </div>
</body>
</html>