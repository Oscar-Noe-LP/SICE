<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; padding: 36px 44px; }

  .header { text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 14px; margin-bottom: 18px; }
  .header .inst-nombre { font-size: 13px; font-weight: bold; text-transform: uppercase; color: #1a3a5c; letter-spacing: 1px; }
  .header .inst-sub    { font-size: 9px; color: #555; margin-top: 2px; }
  .header .doc-titulo  { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-top: 10px; color: #1a3a5c; letter-spacing: 2px; }

  .alumno-grid { display: table; width: 100%; border-collapse: collapse; margin-bottom: 18px; }
  .ag-row { display: table-row; }
  .ag-label { display: table-cell; width: 30%; font-weight: bold; padding: 4px 7px; background: #eef2f7; border: 1px solid #c8d4e0; color: #1a3a5c; font-size: 9.5px; }
  .ag-valor { display: table-cell; padding: 4px 7px; border: 1px solid #c8d4e0; font-size: 9.5px; }

  .tabla-titulo { font-weight: bold; font-size: 11px; color: #1a3a5c; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
  table.califs { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  table.califs thead tr { background: #1a3a5c; color: #fff; }
  table.califs thead th { padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: bold; }
  table.califs thead th.center { text-align: center; }
  table.califs tbody tr:nth-child(even) { background: #f5f8fb; }
  table.califs tbody td { padding: 5px 8px; border-bottom: 1px solid #dde5ee; font-size: 9.5px; }
  table.califs tbody td.center { text-align: center; }
  .aprobado  { color: #1a6e2e; font-weight: bold; }
  .reprobado { color: #c0392b; font-weight: bold; }

  .resumen { background: #eef2f7; border: 1px solid #c8d4e0; border-radius: 4px; padding: 10px 14px; margin-top: 4px; }
  .resumen table { width: 100%; }
  .resumen td { padding: 3px 8px; font-size: 10px; }
  .resumen .res-label { font-weight: bold; color: #1a3a5c; width: 50%; }
  .resumen .res-valor { font-size: 11px; font-weight: bold; }

  .footer { position: fixed; bottom: 18px; left: 44px; right: 44px; border-top: 1px solid #ccc; padding-top: 5px; font-size: 8px; color: #aaa; }
  .footer table { width: 100%; }
</style>
</head>
<body>

<div class="header">
  <div class="inst-nombre">Instituto Tecnológico de Matehuala</div>
  <div class="inst-sub">Tecnológico Nacional de México</div>
  <div class="doc-titulo">Boleta de Calificaciones</div>
</div>

<table class="alumno-grid" style="width:100%; border-collapse:collapse; margin-bottom:18px;">
  <tr>
    <td class="ag-label">Nombre</td>
    <td class="ag-valor">{{ $alumno->nombre_completo }}</td>
    <td class="ag-label">No. control</td>
    <td class="ag-valor">{{ $alumno->numero_control }}</td>
  </tr>
  <tr>
    <td class="ag-label">Carrera</td>
    <td class="ag-valor">{{ $alumno->carrera }}</td>
    <td class="ag-label">Semestre</td>
    <td class="ag-valor">{{ $alumno->semestre_actual }}°</td>
  </tr>
  <tr>
    <td class="ag-label">Periodo</td>
    <td class="ag-valor">{{ $periodo->nombre_periodo }}</td>
    <td class="ag-label">Estatus</td>
    <td class="ag-valor">{{ $alumno->estatus }}</td>
  </tr>
</table>

<div class="tabla-titulo">Calificaciones del periodo</div>
<table class="califs">
  <thead>
    <tr>
      <th>Clave</th>
      <th>Materia</th>
      <th class="center">Créditos</th>
      <th class="center">Calificación</th>
      <th class="center">Resultado</th>
    </tr>
  </thead>
  <tbody>
    @forelse($materias as $m)
    <tr>
      <td>{{ $m->clave }}</td>
      <td>{{ $m->materia }}</td>
      <td class="center">{{ $m->creditos }}</td>
      <td class="center"><strong>{{ number_format($m->calificacion, 1) }}</strong></td>
      <td class="center">
        <span class="{{ $m->resultado === 'A' ? 'aprobado' : 'reprobado' }}">
          {{ $m->resultado === 'A' ? 'Aprobado' : 'Reprobado' }}
        </span>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="5" style="text-align:center; padding:12px; color:#888;">
        Sin materias registradas para este periodo.
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

@if($materias->count() > 0)
<div class="resumen">
  <table>
    <tr>
      <td class="res-label">Total de materias</td>
      <td class="res-valor">{{ $materias->count() }}</td>
      <td class="res-label">Materias aprobadas</td>
      <td class="res-valor aprobado">{{ $materias->where('resultado','A')->count() }}</td>
    </tr>
    <tr>
      <td class="res-label">Promedio del periodo</td>
      <td class="res-valor">{{ number_format($promedioPeriodo, 2) }}</td>
      <td class="res-label">Materias reprobadas</td>
      <td class="res-valor reprobado">{{ $materias->where('resultado','R')->count() }}</td>
    </tr>
  </table>
</div>
@endif

<div style="margin-top:50px;">
  <table style="width:100%;">
    <tr>
      <td style="text-align:center; width:50%;">
        <div style="border-top:1px solid #333; width:200px; margin:0 auto 5px;"></div>
        <div style="font-weight:bold; font-size:9px; text-transform:uppercase;">Jefe(a) de Servicios Escolares</div>
      </td>
      <td style="text-align:center; width:50%;">
        <div style="border-top:1px solid #333; width:200px; margin:0 auto 5px;"></div>
        <div style="font-weight:bold; font-size:9px; text-transform:uppercase;">Firma del Alumno(a)</div>
      </td>
    </tr>
  </table>
</div>

<div class="footer">
  <table>
    <tr>
      <td>Generado por SICE — Sistema Institucional de Control Escolar</td>
      <td style="text-align:right;">{{ now()->format('d/m/Y H:i') }}</td>
    </tr>
  </table>
</div>

</body>
</html>