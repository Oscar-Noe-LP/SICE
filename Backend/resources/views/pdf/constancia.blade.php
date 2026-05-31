<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $tipo_documento }} - {{ $numero_control }}</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1D52B7;
            padding-bottom: 20px;
        }
        .tipo-documento {
            font-size: 22px;
            font-weight: bold;
            color: #1D52B7;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }
        .subtitulo {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }
        .folio {
            font-size: 10px;
            color: #999;
            margin-top: 8px;
        }
        .content {
            margin: 30px 0;
        }
        .constancia-texto {
            text-align: justify;
            margin: 20px 0;
        }
        .datos-alumno {
            margin: 25px 0;
            padding: 15px;
            background: #f5f5f5;
            border-left: 4px solid #1D52B7;
        }
        .firma {
            margin-top: 50px;
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .watermark {
            position: fixed;
            bottom: 50px;
            right: 30px;
            opacity: 0.08;
            font-size: 45px;
            transform: rotate(-45deg);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="watermark">{{ $tipo_documento }}</div>

    <div class="header">
        <div class="tipo-documento">{{ $tipo_documento }}</div>
        <div class="subtitulo">{{ $subtitulo }}</div>
        <div class="folio">Folio: {{ $folio }}</div>
        <div class="folio">Fecha de emisión: {{ $fecha_emision->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="content">
        <p style="text-align: right; margin-bottom: 20px;">Oficio No. SICE/{{ $tipo_documento }}/{{ date('Y') }}/{{ $numero_control }}</p>

        <p><strong>ASUNTO:</strong> {{ $tipo_texto }}</p>

        <div class="constancia-texto">
            <p>La Dirección de Servicios Escolares del Instituto Tecnológico, <strong>HACE CONSTAR</strong> que:</p>
        </div>

        <div class="datos-alumno">
            <p><strong>Nombre del alumno(a):</strong> {{ $nombre_completo }}</p>
            <p><strong>Número de control:</strong> {{ $numero_control }}</p>
            <p><strong>Carrera:</strong> {{ $carrera }}</p>
            <p><strong>Semestre actual:</strong> {{ $semestre }}° Semestre</p>
            @if($periodo)
            <p><strong>Periodo escolar:</strong> {{ $periodo->nombre_periodo }}</p>
            @endif
            @if($promedio)
            <p><strong>Promedio general:</strong> {{ number_format($promedio, 2) }}</p>
            @endif
        </div>

        <div class="constancia-texto">
            <p>Se expide la presente <strong>{{ $tipo_texto }}</strong> a solicitud del interesado para los fines que a él convengan, en la ciudad de México, a los {{ $fecha_emision->format('d') }} días del mes de {{ $fecha_emision->format('F') }} de {{ $fecha_emision->format('Y') }}.</p>
        </div>
    </div>

    <div class="firma">
        <p>ATENTAMENTE</p>
        <p style="margin-top: 40px;">_______________________________</p>
        <p><strong>DIRECCIÓN DE SERVICIOS ESCOLARES</strong></p>
    </div>

    <div class="footer">
        <p>Documento generado por el Sistema de Control Escolar (SICE)</p>
        <p><strong>Tipo de documento: {{ $tipo_documento }} - {{ $tipo_texto }}</strong></p>
        <p>Este documento es una constancia oficial con validez institucional</p>
    </div>
</body>
</html>
