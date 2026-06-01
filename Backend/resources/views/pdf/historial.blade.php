<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historial</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2, h3 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
    </style>
</head>
<body>

<h2>Historial de Inscripciones</h2>

<p><strong>Alumno:</strong> {{ $alumno->nombre }}</p>
<p><strong>No. Control:</strong> {{ $alumno->numero_control }}</p>

@foreach($periodos as $periodo)
    <h3>{{ $periodo->nombre_periodo }}</h3>

    <table>
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Materia</th>
                <th>Docente</th>
                <th>Calificación</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periodo->inscripciones as $ins)
                <tr>
                    <td>{{ $ins->clave_grupo }}</td>
                    <td>{{ $ins->nombre_materia }}</td>
                    <td>{{ $ins->nombre_docente ?? '—' }}</td>
                    <td>{{ $ins->calificacion_final ?? '—' }}</td>
                    <td>{{ $ins->estatus }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

</body>
</html>
