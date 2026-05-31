<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1a1a1a; padding: 32px 40px; }

  .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 12px; margin-bottom: 16px; }
  .header .inst { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #1a3a5c; letter-spacing: 1px; }
  .header .sub  { font-size: 9px; color: #666; margin-top: 2px; }
  .header .titulo { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-top: 10px; color: #1a3a5c; letter-spacing: 2px; }

  /* Datos alumno */
  .alumno-grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  .alumno-grid td { padding: 4px 8px; border: 1px solid #c8d4e0; font-size: 9.5px; }
  .alumno-grid .lbl { background: #eef2f7; font-weight: bold; color: #1a3a5c; width: 20%; }

  /* KPIs */
  .kpis { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .kpis td { border: 1px solid #c8d4e0; text-align: center; padding: 8px 4px; }
  .kpi-valor { font-size: 18px; font-weight: bold; color: #1a3a5c; display: block; }
  .kpi-label { font-size: 8.5px; color: #666; display: block; margin-top: 2px; }
  .kpi-verde  .kpi-valor { color: #1a6e2e; }
  .kpi-rojo   .kpi-valor { color: #c0392b; }
  .kpi-naranja .kpi-valor { color: #d35400; }

  /* Barra créditos */
  .creditos-bar-wrap { margin-bottom: 16px; }
  .creditos-label { font-size: 9px; color: #555; margin-bottom: 4px; display: flex; justify-content: space-between; }
  .bar-outer { width: 100%; height: 8px; background: #e8edf4; border-radius: 4px; overflow: hidden; }
  .bar-inner { height: 100%; background: #1a3a5c; border-radius: 4px; }

  /* Semestres */
  .semestre-bloque { margin-bottom: 12px; page-break-inside: avoid; }
  .sem-header { background: #1a3a5c; color: #fff; padding: 5px 8px; display: flex; justify-content: space-between; align-items: center; border-radius: 3px 3px 0 0; }
  .sem-titulo { font-weight: bold; font-size: 10px; }
  .sem-stats  { font-size: 9px; opacity: .85; }

  table.materias { width: 100%; border-collapse: collapse; }
  table.materias thead tr { background: #dde5ee; }
  table.materias thead th { padding: 4px 6px; text-align: left; font-size: 9px; font-weight: bold; color: #1a3a5c; }
  table.materias thead th.c { text-align: center; }
  table.materias tbody tr:nth-child(even) { background: #f7f9fb; }
  table.materias tbody td { padding: 4px 6px; border-bottom: 1px solid #e4eaf0; font-size: 9px; }
  table.materias tbody td.c { text-align: center; }

  .est-acreditada { color: #1a6e2e; font-weight: bold; }
  .est-reprobada  { color: #c0392b; font-weight: bold; }
  .est-pendiente  { color: #d35400; }
  .est-no_cursada { color: #aaa; font-style: italic; }

  .sem-promedio { text-align: right; padding: 4px 8px; font-size: 9px; color: #555; background: #f0f5fc; border: 1px solid #dde5ee; border-top: none; border-radius: 0 0 3px 3px; }

  /* Firma */
  .firma-area { margin-top: 40px; width: 100%; border-collapse: collapse; }
  .firma-area td { text-align: center; padding: 0 20px; }
  .firma-linea { border-top: 1px solid #333; width: 200px; margin: 0 auto 5px; }
  .firma-nombre { font-weight: bold; font-size: 9px; text-transform: uppercase; }
  .firma-cargo  { font-size: 8.5px; color: #666; }

  .footer { position: fixed; bottom: 16px; left: 40px; right: 40px; border-top: 1px solid #ccc; padding-top: 5px; font-size: 8px; color: #aaa; }
  .footer table { width: 100%; }
</style>
</head>
<body>

<div class="header">
  <div class="inst">Instituto Tecnológico de Matehuala</div>
  <div class="sub">Tecnológico Nacional de México</div>
  <div class="titulo">Kardex Académico</div>
</div>

{{-- Datos del alumno --}}
<table class="alumno-grid">
  <tr>
    <td class="lbl">Nombre</td>
    <td colspan="3">{{ $alumno['nombre'] }}</td>
  </tr>
  <tr>
    <td class="lbl">No. control</td>
    <td>{{ $alumno['no_control'] }}</td>
    <td class="lbl">CURP</td>
    <td>{{ $alumno['curp'] ?? '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Carrera</td>
    <td colspan="3">{{ $alumno['carrera'] }}</td>
  </tr>
  <tr>
    <td class="lbl">Plan de estudios</td>
    <td>{{ $alumno['plan_estudio'] }}</td>
    <td class="lbl">Semestre actual</td>
    <td>{{ $alumno['semestre_actual'] ?? '—' }}°</td>
  </tr>
  <tr>
    <td class="lbl">Estatus</td>
    <td>{{ $alumno['estatus'] }}</td>
    <td class="lbl">Fecha generación</td>
    <td>{{ $fechaGeneracion }}</td>
  </tr>
</table>

{{-- KPIs --}}
<table class="kpis">
  <tr>
    <td>
      <span class="kpi-valor">{{ $totalMaterias }}</span>
      <span class="kpi-label">Total materias</span>
    </td>
    <td class="kpi-verde">
      <span class="kpi-valor">{{ $totalAcreditadas }}</span>
      <span class="kpi-label">Acreditadas</span>
    </td>
    <td class="kpi-rojo">
      <span class="kpi-valor">{{ $totalReprobadas }}</span>
      <span class="kpi-label">Reprobadas</span>
    </td>
    <td class="kpi-naranja">
      <span class="kpi-valor">{{ $totalMaterias - $totalAcreditadas - $totalReprobadas }}</span>
      <span class="kpi-label">No cursadas</span>
    </td>
    <td>
      <span class="kpi-valor">{{ $promedioGeneral ?? '—' }}</span>
      <span class="kpi-label">Promedio general</span>
    </td>
  </tr>
</table>

{{-- Barra de créditos --}}
@php
  $pct = $alumno['creditos_totales'] > 0
    ? round(($alumno['creditos_acumulados'] / $alumno['creditos_totales']) * 100)
    : 0;
@endphp
<div class="creditos-bar-wrap">
  <div class="creditos-label">
    <span>Avance en créditos: {{ $pct }}%</span>
    <span>{{ $alumno['creditos_acumulados'] }} de {{ $alumno['creditos_totales'] }} créditos</span>
  </div>
  <div class="bar-outer">
    <div class="bar-inner" style="width: {{ $pct }}%;"></div>
  </div>
</div>

{{-- Semestres --}}
@foreach($semestres as $sem)
<div class="semestre-bloque">
  <div class="sem-header">
    <span class="sem-titulo">Semestre {{ $sem['numero'] }} &nbsp;·&nbsp; {{ count($sem['materias']) }} materias</span>
    <span class="sem-stats">
      ✓ {{ $sem['acreditadas'] }} acreditadas
      &nbsp;|&nbsp;
      ✗ {{ $sem['reprobadas'] }} reprobadas
    </span>
  </div>
  <table class="materias">
    <thead>
      <tr>
        <th style="width:12%">Clave</th>
        <th>Materia</th>
        <th class="c" style="width:9%">Créditos</th>
        <th class="c" style="width:12%">Calificación</th>
        <th class="c" style="width:14%">Estado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($sem['materias'] as $mat)
      <tr>
        <td>{{ $mat['clave'] }}</td>
        <td>{{ $mat['nombre'] }}</td>
        <td class="c">{{ $mat['creditos'] }}</td>
        <td class="c">
          @if($mat['calificacion'] !== null)
            <strong>{{ number_format($mat['calificacion'], 1) }}</strong>
          @else
            —
          @endif
        </td>
        <td class="c est-{{ $mat['estado'] }}">
          {{ match($mat['estado']) {
            'acreditada' => 'Acreditada',
            'reprobada'  => 'Reprobada',
            'pendiente'  => 'Pendiente',
            default      => 'No cursada',
          } }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="sem-promedio">
    Promedio del semestre:
    <strong>{{ $sem['promedio'] !== null ? number_format($sem['promedio'], 2) : '—' }}</strong>
  </div>
</div>
@endforeach

{{-- Firmas --}}
<table class="firma-area" style="margin-top:40px;">
  <tr>
    <td>
      <div class="firma-linea"></div>
      <div class="firma-nombre">Director(a)</div>
      <div class="firma-cargo">Instituto Tecnológico de Matehuala</div>
    </td>
    <td>
      <div class="firma-linea"></div>
      <div class="firma-nombre">Jefe(a) de Servicios Escolares</div>
      <div class="firma-cargo">Control Escolar</div>
    </td>
  </tr>
</table>

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