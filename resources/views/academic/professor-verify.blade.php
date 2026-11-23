<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Profesor</title>
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
        }
        h1 { color: #c4b5fd; margin-bottom: 14px; font-size: 1.4rem; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; color: #ddd6fe; font-weight: bold; }
        input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #3f3b5c;
            background-color: #14131f;
            color: #f3f4f6;
        }
        .btn {
            width: 100%;
            padding: 11px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            background-color: #6f42c1;
            color: #ffffff;
            margin-top: 6px;
        }
        .btn-secondary {
            display: block;
            text-align: center;
            text-decoration: none;
            background-color: #2d2b3e;
            color: #e5e7eb;
            padding: 10px 16px;
            border-radius: 8px;
            margin-top: 12px;
            font-weight: bold;
        }
        .status {
            background-color: #1e3a2f;
            color: #a7f3d0;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 0.9rem;
        }
        .error {
            background-color: #3f1d24;
            color: #fecaca;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 0.9rem;
        }
        hr { border: none; border-top: 1px solid #2e2c42; margin: 20px 0; }
    </style>
    @include('components.minimal-theme')
</head>
<body>
    <div class="card">
        <h1>Acceso de Profesor</h1>

        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('academic.professor.send') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Correo del profesor (Gmail)</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" placeholder="profesor@gmail.com" required>
            </div>
            <button type="submit" class="btn">Enviar código al correo</button>
        </form>

        <hr>

        <form action="{{ route('academic.professor.verify') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="code">Código de 6 dígitos</label>
                <input type="text" id="code" name="code" maxlength="6" placeholder="123456" required>
            </div>
            <button type="submit" class="btn">Verificar y entrar</button>
        </form>

        <a href="{{ route('modules.academic') }}" class="btn-secondary">Cambiar rol</a>
    </div>
</body>
</html>