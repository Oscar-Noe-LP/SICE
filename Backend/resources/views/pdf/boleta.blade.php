<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $tipo_documento }} - {{ $numero_control }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1D52B7;
            padding-bottom: 10px;
        }
        .tipo-documento {
            font-size: 18px;
            font-weight: bold;
            color: #1D52B7;
        }
        .info-alumno {
            margin: 15px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #1D52B7;
            color: white;
        }
        .aprobada { color: green; font-weight: bold; }
        .reprobada { color: red; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="tipo-documento">{{ $tipo_documento }}</div>
        <div>Folio: {{ $folio }}</div>
        <div>Fecha: {{ $fecha_emision->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="info-alumno">
        <p><strong>Número de control:</strong> {{ $numero_control }}</p>
        <p><strong>Nombre:</strong> {{ $nombre_completo }}</p>
        <p><strong>Carrera:</strong> {{ $carrera }}</p>
        <p><strong>Semestre:</strong> {{ $semestre }}°</p>
        @if($periodo)
        <p><strong>Periodo:</strong> {{ $periodo->nombre_periodo ?? $periodo->id_periodo }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Clave</th>
                <th>Materia</th>
                <th>Créditos</th>
                <th>Calificación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materias as $materia)
            <tr>
                <td>{{ $materia->clave }}</td>
                <td>{{ $materia->nombre }}</td>
                <td>{{ $materia->creditos }}</td>
                <td>{{ $materia->calificacion }}</td>
                <td class="{{ strtolower($materia->estado) }}">{{ $materia->estado }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">No hay materias registradas</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Promedio del periodo: {{ number_format($promedio_periodo, 2) }}</strong></p>
        <p>Tipo de documento: {{ $tipo_documento }}</p>
    </div>
</body>
</html>
