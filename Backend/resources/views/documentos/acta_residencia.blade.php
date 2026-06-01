<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'DejaVu Sans',sans-serif; font-size:10.5px; color:#1a1a1a; padding:40px 50px; }
  .header { text-align:center; border-bottom:2px solid #1a3a5c; padding-bottom:14px; margin-bottom:20px; }
  .inst   { font-size:13px; font-weight:bold; text-transform:uppercase; color:#1a3a5c; letter-spacing:1px; }
  .sub    { font-size:9px; color:#666; margin-top:2px; }
  .titulo { font-size:15px; font-weight:bold; text-transform:uppercase; margin-top:10px; color:#1a3a5c; letter-spacing:2px; }
  .folio  { text-align:right; font-size:9px; color:#999; margin-bottom:18px; }
  table.datos { width:100%; border-collapse:collapse; margin-bottom:14px; }
  table.datos td { padding:5px 8px; border:1px solid #c8d4e0; font-size:10px; }
  table.datos td.lbl { background:#eef2f7; font-weight:bold; color:#1a3a5c; width:28%; }
  .seccion { font-weight:bold; font-size:10px; color:#1a3a5c; text-transform:uppercase;
             background:#dde5ee; padding:5px 8px; margin:12px 0 0; letter-spacing:.5px; }
  table.calif { width:100%; border-collapse:collapse; margin-bottom:14px; background:#eef2f7; border:1px solid #c8d4e0; }
  table.calif td { padding:5px 10px; font-size:10.5px; }
  table.calif td.cl { font-weight:bold; color:#1a3a5c; width:40%; }
  table.calif td.cv { font-size:12px; font-weight:bold; }
  .aprobado  { color:#1a6e2e; }
  .reprobado { color:#c0392b; }
  table.firmas { width:100%; margin-top:50px; }
  table.firmas td { text-align:center; padding:0 15px; width:33%; }
  .firma-linea { border-top:1px solid #333; width:170px; margin:0 auto 5px; }
</style>
</head>
<body>

<div class="header">
  <div class="inst">Instituto Tecnológico de Matehuala</div>
  <div class="sub">Tecnológico Nacional de México</div>
  <div class="titulo">Acta de Residencia Profesional</div>
</div>

<div class="folio">Folio: {{ $folio }} &nbsp;|&nbsp; {{ now()->format('d/m/Y') }}</div>

<div class="seccion">Datos del Alumno</div>
<table class="datos">
  <tr>
    <td class="lbl">Nombre</td>
    <td colspan="3">{{ $solicitud->nombre_alumno }}</td>
  </tr>
  <tr>
    <td class="lbl">No. de control</td>
    <td>{{ $solicitud->numero_control }}</td>
    <td class="lbl">CURP</td>
    <td>{{ $solicitud->curp ?? '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Carrera</td>
    <td colspan="3">{{ $solicitud->carrera }}</td>
  </tr>
  <tr>
    <td class="lbl">Periodo</td>
    <td>{{ $solicitud->periodo }}</td>
    <td class="lbl">Estatus</td>
    <td>{{ $solicitud->estatus }}</td>
  </tr>
</table>

@if($proyecto)
<div class="seccion">Proyecto y Empresa</div>
<table class="datos">
  <tr>
    <td class="lbl">Proyecto</td>
    <td colspan="3">{{ $proyecto->nombre_proyecto }}</td>
  </tr>
  <tr>
    <td class="lbl">Empresa</td>
    <td>{{ $proyecto->empresa }}</td>
    <td class="lbl">Giro</td>
    <td>{{ $proyecto->giro ?? '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Ciudad</td>
    <td>{{ $proyecto->ciudad ?? '—' }}</td>
    <td class="lbl">Horas</td>
    <td>{{ $proyecto->horas_acumuladas }} / {{ $proyecto->horas_requeridas }}</td>
  </tr>
  <tr>
    <td class="lbl">Asesor externo</td>
    <td>{{ $proyecto->asesor_externo ?? '—' }} {{ $proyecto->puesto_asesor ? '('.$proyecto->puesto_asesor.')' : '' }}</td>
    <td class="lbl">Asesor interno</td>
    <td>{{ $proyecto->asesor_interno }}</td>
  </tr>
  <tr>
    <td class="lbl">Fecha inicio</td>
    <td>{{ $proyecto->fecha_inicio ? \Carbon\Carbon::parse($proyecto->fecha_inicio)->format('d/m/Y') : '—' }}</td>
    <td class="lbl">Fecha fin</td>
    <td>{{ $proyecto->fecha_fin_real ? \Carbon\Carbon::parse($proyecto->fecha_fin_real)->format('d/m/Y') : ($proyecto->fecha_fin_estimada ? \Carbon\Carbon::parse($proyecto->fecha_fin_estimada)->format('d/m/Y').' (est.)' : '—') }}</td>
  </tr>
  <tr>
    <td class="lbl">Carta de liberación</td>
    <td colspan="3">{{ $proyecto->carta_liberacion ? 'Emitida' : 'Pendiente' }}</td>
  </tr>
</table>

@if($proyecto->calificacion_final !== null)
<div class="seccion">Evaluación Final</div>
<table class="calif">
  <tr>
    <td class="cl">Calificación final</td>
    <td class="cv {{ $proyecto->calificacion_final >= 70 ? 'aprobado' : 'reprobado' }}">
      {{ number_format($proyecto->calificacion_final, 1) }}
      — {{ $proyecto->calificacion_final >= 70 ? 'ACREDITADA' : 'NO ACREDITADA' }}
    </td>
  </tr>
</table>
@endif
@endif

<table class="firmas">
  <tr>
    <td>
      <div class="firma-linea"></div>
      <div style="font-weight:bold;font-size:9px;text-transform:uppercase;">Director(a)</div>
      <div style="font-size:8.5px;color:#666;">Instituto Tecnológico de Matehuala</div>
    </td>
    <td>
      <div class="firma-linea"></div>
      <div style="font-weight:bold;font-size:9px;text-transform:uppercase;">Jefe(a) de Servicios Escolares</div>
    </td>
    <td>
      <div class="firma-linea"></div>
      <div style="font-weight:bold;font-size:9px;text-transform:uppercase;">Firma del Alumno(a)</div>
    </td>
  </tr>
</table>

<table style="width:100%;position:fixed;bottom:16px;left:50px;right:50px;border-top:1px solid #ccc;">
  <tr>
    <td style="font-size:8px;color:#aaa;">Generado por SICE — Sistema Institucional de Control Escolar</td>
    <td style="font-size:8px;color:#aaa;text-align:right;">{{ now()->format('d/m/Y H:i') }}</td>
  </tr>
</table>

</body>
</html>
