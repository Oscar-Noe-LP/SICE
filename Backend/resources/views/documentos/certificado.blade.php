<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1a1a1a; padding: 34px 42px; }

  .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 12px; margin-bottom: 16px; }
  .header .inst-nombre { font-size: 13px; font-weight: bold; text-transform: uppercase; color: #1a3a5c; }
  .header .inst-sub    { font-size: 9px; color: #666; margin-top: 2px; }
  .header .doc-titulo  { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-top: 10px; color: #1a3a5c; letter-spacing: 2px; }

  .alumno-datos { margin-bottom: 16px; }
  .alumno-datos table { width: 100%; border-collapse: collapse; }
  .alumno-datos td { padding: 4px 7px; border: 1px solid #c8d4e0; font-size: 9.5px; }
  .alumno-datos .label { background: #eef2f7; font-weight: bold; color: #1a3a5c; width: 22%; }

  .semestre-titulo { background: #1a3a5c; color: #fff; font-weight: bold; font-size: 10px; padding: 5px 8px; margin-bottom: 0; }
  table.materias { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  table.materias thead tr { background: #dde5ee; }
  table.materias thead th { padding: 4px 6px; text-align: left; font-size: 9px; font-weight: bold; color: #1a3a5c; }
  table.materias thead th.c { text-align: center; }
  table.materias tbody tr:nth-child(even) { background: #f7f9fb; }
  table.materias tbody td { padding: 4px 6px; border-bottom: 1px solid #e4eaf0; font-size: 9px; }
  table.materias tbody td.c { text-align: center; }
  .acreditada { color: #1a6e2e; }
  .reprobada  { color: #c0392b; }
  .pendiente  { color: #888; font-style: italic; }

  .totales { background: #eef2f7; border: 1px solid #c8d4e0; padding: 10px 14px; margin-top: 6px; border-radius: 3px; }
  .totales table { width: 100%; }
  .totales td { padding: 3px 6px; font-size: 10px; }
  .totales .tl { font-weight: bold; color: #1a3a5c; }
  .totales .tv { font-weight: bold; font-size: 11px; }

  .footer { position: fixed; bottom: 16px; left: 42px; right: 42px; border-top: 1px solid #ccc; padding-top: 5px; font-size: 8px; color: #aaa; }
  .footer table { width: 100%; }

  .firma-block { margin-top: 44px; }
  .firma-block table { width: 100%; }
  .firma-block td { text-align: center; padding: 0 20px; }
  .firma-linea { border-top: 1px solid #333; width: 200px; margin: 0 auto 5px; }
</style>
</head>
<body>

<div class="header">
  <div class="inst-nombre">Instituto Tecnológico de Matehuala</div>
  <div class="inst-sub">Tecnológico Nacional de México</div>
  <div class="doc-titulo">Certificado de Estudios</div>
</div>

<div class="alumno-datos">
  <table>
    <tr>
      <td class="label">Nombre completo</td>
      <td colspan="3">{{ $alumno->nombre_completo }}</td>
    </tr>
    <tr>
      <td class="label">No. de control</td>
      <td>{{ $alumno->numero_control }}</td>
      <td class="label">CURP</td>
      <td>{{ $alumno->curp ?? '—' }}</td>
    </tr>
    <tr>
      <td class="label">Carrera</td>
      <td colspan="3">{{ $alumno->carrera }}</td>
    </tr>
    <tr>
      <td class="label">Plan de estudios</td>
      <td>{{ $alumno->nombre_plan ?? 'Plan Vigente' }}</td>
      <td class="label">Semestre actual</td>
      <td>{{ $alumno->semestre_actual }}°</td>
    </tr>
    <tr>
      <td class="label">Fecha de ingreso</td>
      <td>{{ $alumno->fecha_ingreso ? \Carbon\Carbon::parse($alumno->fecha_ingreso)->format('d/m/Y') : '—' }}</td>
      <td class="label">Estatus</td>
      <td>{{ $alumno->estatus }}</td>
    </tr>
  </table>
</div>

@foreach($materiasPorSemestre as $semestre => $materias)
<div class="semestre-titulo">{{ $semestre }}° Semestre</div>
<table class="materias">
  <thead>
    <tr>
      <th style="width:12%">Clave</th>
      <th>Materia</th>
      <th class="c" style="width:8%">H.T.</th>
      <th class="c" style="width:8%">H.P.</th>
      <th class="c" style="width:8%">Créd.</th>
      <th class="c" style="width:12%">Calificación</th>
      <th class="c" style="width:12%">Estado</th>
    </tr>
  </thead>
  <tbody>
    @foreach($materias as $m)
    <tr>
      <td>{{ $m->clave }}</td>
      <td>{{ $m->materia }}</td>
      <td class="c">{{ $m->horas_teoria }}</td>
      <td class="c">{{ $m->horas_practica }}</td>
      <td class="c">{{ $m->creditos }}</td>
      <td class="c">
        @if($m->calificacion !== null)
          <strong>{{ number_format($m->calificacion, 1) }}</strong>
        @else
          —
        @endif
      </td>
      <td class="c {{ strtolower($m->estado) }}">{{ $m->estado }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endforeach

<div class="totales">
  <table>
    <tr>
      <td class="tl">Créditos acumulados</td>
      <td class="tv">{{ $creditosAcumulados }} / {{ $alumno->total_creditos ?? '—' }}</td>
      <td class="tl">Promedio general</td>
      <td class="tv">{{ number_format($promedioGeneral, 2) }}</td>
    </tr>
  </table>
</div>

<div class="firma-block">
  <table>
    <tr>
      <td>
        <div class="firma-linea"></div>
        <div style="font-weight:bold; font-size:9px; text-transform:uppercase;">Director(a)</div>
        <div style="font-size:8.5px; color:#666;">Instituto Tecnológico de Matehuala</div>
      </td>
      <td>
        <div class="firma-linea"></div>
        <div style="font-weight:bold; font-size:9px; text-transform:uppercase;">Jefe(a) de Servicios Escolares</div>
        <div style="font-size:8.5px; color:#666;">Control Escolar</div>
      </td>
      <td>
        <div class="firma-linea"></div>
        <div style="font-weight:bold; font-size:9px; text-transform:uppercase;">Firma del Alumno(a)</div>
      </td>
    </tr>
  </table>
</div>

<div class="footer">
  <table>
    <tr>
      <td>Documento generado por SICE — Sistema Institucional de Control Escolar</td>
      <td style="text-align:right;">{{ now()->format('d/m/Y H:i') }}</td>
    </tr>
  </table>
</div>

</body>
</html>