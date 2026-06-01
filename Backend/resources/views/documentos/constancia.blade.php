<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; padding: 40px 50px; }

  .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 16px; margin-bottom: 24px; }
  .header .inst-nombre { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1a3a5c; }
  .header .inst-sub { font-size: 10px; color: #555; margin-top: 2px; }
  .header .doc-titulo { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 12px; color: #1a3a5c; letter-spacing: 2px; }

  .folio { text-align: right; font-size: 9px; color: #888; margin-bottom: 20px; }

  .cuerpo { line-height: 1.9; font-size: 11.5px; text-align: justify; margin-bottom: 28px; }
  .cuerpo strong { font-weight: bold; text-transform: uppercase; }

  .datos-grid { display: table; width: 100%; margin: 20px 0; border-collapse: collapse; }
  .dato-row { display: table-row; }
  .dato-label { display: table-cell; width: 38%; font-weight: bold; padding: 5px 8px; background: #f0f4f8; border: 1px solid #d0d8e0; color: #1a3a5c; }
  .dato-valor { display: table-cell; padding: 5px 8px; border: 1px solid #d0d8e0; }

  .expide { margin-top: 32px; font-size: 10.5px; color: #444; text-align: justify; }

  .firma-area { margin-top: 60px; display: table; width: 100%; }
  .firma-col { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
  .firma-linea { border-top: 1px solid #333; width: 200px; margin: 0 auto 6px; }
  .firma-nombre { font-weight: bold; font-size: 10px; text-transform: uppercase; }
  .firma-cargo { font-size: 9px; color: #666; }

  .sello { position: fixed; bottom: 60px; right: 50px; width: 90px; height: 90px; border: 3px solid #1a3a5c; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 8px; color: #1a3a5c; font-weight: bold; opacity: 0.25; padding: 10px; }

  .footer { position: fixed; bottom: 20px; left: 50px; right: 50px; border-top: 1px solid #ccc; padding-top: 6px; font-size: 8.5px; color: #999; display: table; width: calc(100% - 100px); }
  .footer-izq { display: table-cell; text-align: left; }
  .footer-der { display: table-cell; text-align: right; }
</style>
</head>
<body>

<div class="header">
  <div class="inst-nombre">Instituto Tecnológico de Matehuala</div>
  <div class="inst-sub">Tecnológico Nacional de México</div>
  <div class="doc-titulo">{{ $titulo }}</div>
</div>

<div class="folio">Folio: {{ strtoupper(substr(md5($alumno->numero_control . now()->format('Ymd')), 0, 10)) }} &nbsp;|&nbsp; Fecha: {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</div>

<div class="cuerpo">
  <p>
    La <strong>Dirección del Instituto Tecnológico de Matehuala</strong>, perteneciente al Tecnológico Nacional de México,
    hace constar que el (la) C. <strong>{{ $alumno->nombre_completo }}</strong>,
    con número de control <strong>{{ $alumno->numero_control }}</strong>
    @if($alumno->curp)
      y CURP <strong>{{ $alumno->curp }}</strong>,
    @endif
    se encuentra {!! $tipo === 'inscripcion' ? 'debidamente <strong>inscrito(a)</strong>' : '<strong>cursando</strong>' !!}
    el <strong>{{ $alumno->semestre_actual }}° semestre</strong>
    de la carrera de <strong>{{ $alumno->carrera }}</strong>.

    @if($tipo === 'promedio' && $promedio)
      <br><br>
      El promedio general acumulado del alumno al momento de la expedición del presente documento es de
      <strong>{{ number_format($promedio, 2) }}</strong> ({{ $promedio >= 90 ? 'Excelente' : ($promedio >= 80 ? 'Muy Bueno' : ($promedio >= 70 ? 'Bueno' : 'Regular')) }}).
    @endif
  </p>
</div>

<table class="datos-grid" style="width:100%; border-collapse:collapse;">
  <tr>
    <td class="dato-label">Nombre completo</td>
    <td class="dato-valor">{{ $alumno->nombre_completo }}</td>
  </tr>
  <tr>
    <td class="dato-label">Número de control</td>
    <td class="dato-valor">{{ $alumno->numero_control }}</td>
  </tr>
  @if($alumno->curp)
  <tr>
    <td class="dato-label">CURP</td>
    <td class="dato-valor">{{ $alumno->curp }}</td>
  </tr>
  @endif
  <tr>
    <td class="dato-label">Carrera</td>
    <td class="dato-valor">{{ $alumno->carrera }}</td>
  </tr>
  <tr>
    <td class="dato-label">Semestre actual</td>
    <td class="dato-valor">{{ $alumno->semestre_actual }}°</td>
  </tr>
  <tr>
    <td class="dato-label">Periodo vigente</td>
    <td class="dato-valor">{{ $periodo->nombre_periodo ?? 'N/D' }}</td>
  </tr>
  <tr>
    <td class="dato-label">Estatus</td>
    <td class="dato-valor">{{ $alumno->estatus }}</td>
  </tr>
  @if($tipo === 'promedio' && $promedio)
  <tr>
    <td class="dato-label">Promedio general</td>
    <td class="dato-valor"><strong>{{ number_format($promedio, 2) }}</strong></td>
  </tr>
  @endif
</table>

<div class="expide">
  La presente constancia se expide a petición del interesado(a), para los fines que estime convenientes,
  en Matehuala, San Luis Potosí, a {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}.
</div>

<div class="firma-area" style="margin-top:60px; width:100%;">
  <table style="width:100%;">
    <tr>
      <td style="text-align:center; width:50%;">
        <div style="border-top:1px solid #333; width:200px; margin:0 auto 6px;"></div>
        <div style="font-weight:bold; font-size:10px; text-transform:uppercase;">Director(a) de la Institución</div>
        <div style="font-size:9px; color:#666;">Instituto Tecnológico de Matehuala</div>
      </td>
      <td style="text-align:center; width:50%;">
        <div style="border-top:1px solid #333; width:200px; margin:0 auto 6px;"></div>
        <div style="font-weight:bold; font-size:10px; text-transform:uppercase;">Jefe(a) de Servicios Escolares</div>
        <div style="font-size:9px; color:#666;">Control Escolar</div>
      </td>
    </tr>
  </table>
</div>

<div class="footer">
  <table style="width:100%;">
    <tr>
      <td style="text-align:left; font-size:8.5px; color:#999;">Documento generado por SICE — Sistema Institucional de Control Escolar</td>
      <td style="text-align:right; font-size:8.5px; color:#999;">{{ now()->format('d/m/Y H:i') }}</td>
    </tr>
  </table>
</div>

</body>
</html>