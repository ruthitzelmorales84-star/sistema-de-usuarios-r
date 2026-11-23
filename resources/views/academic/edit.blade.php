<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar alumno</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #1c1b29;
            border: 1px solid #312e4a;
            border-radius: 12px;
            padding: 28px;
        }
        h1 { color: #c4b5fd; margin-bottom: 18px; }
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
        .actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            background-color: #6f42c1;
            color: #ffffff;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .btn-secondary { background-color: #2d2b3e; color: #e5e7eb; }
        .alert-error {
            padding: 12px 16px;
            border-radius: 8px;
            background-color: #3f1d24;
            color: #fecaca;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar alumno</h1>

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('academic.update', $academicRecord) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="student_name">Nombre del alumno</label>
                <input type="text" id="student_name" name="student_name" value="{{ old('student_name', $academicRecord->student_name) }}" required>
            </div>

            <div class="form-group">
                <label for="student_email">Correo del alumno (opcional)</label>
                <input type="email" id="student_email" name="student_email" value="{{ old('student_email', $academicRecord->student_email) }}">
            </div>

            <div class="form-group">
                <label for="absences">Número de faltas (0 - 100)</label>
                <input type="number" id="absences" name="absences" min="0" max="100" value="{{ old('absences', $academicRecord->absences) }}" required>
            </div>

            <div class="form-group">
                <label for="grade">Calificación numérica (0.0 - 10.0)</label>
                <input type="number" step="0.1" id="grade" name="grade" min="0" max="10" value="{{ old('grade', $academicRecord->grade) }}" required>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Guardar cambios</button>
                <a href="{{ route('academic.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>
