<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#1a1a1a; padding:36px 44px; }
  .header { text-align:center; border-bottom:2px solid #1a3a5c; padding-bottom:12px; margin-bottom:16px; }
  .inst    { font-size:12px; font-weight:bold; text-transform:uppercase; color:#1a3a5c; }
  .sub     { font-size:9px; color:#666; margin-top:2px; }
  .titulo  { font-size:14px; font-weight:bold; text-transform:uppercase; margin-top:8px; color:#1a3a5c; letter-spacing:2px; }
  table.alumno { width:100%; border-collapse:collapse; margin-bottom:14px; }
  table.alumno td { padding:4px 7px; border:1px solid #c8d4e0; font-size:9.5px; }
  table.alumno td.lbl { background:#eef2f7; font-weight:bold; color:#1a3a5c; width:22%; }
  table.materias { width:100%; border-collapse:collapse; margin-bottom:14px; }
  table.materias thead tr { background:#1a3a5c; color:#fff; }
  table.materias thead th { padding:5px 7px; text-align:left; font-size:9px; font-weight:bold; }
  table.materias thead th.c { text-align:center; }
  table.materias tbody tr:nth-child(even) { background:#f5f8fb; }
  table.materias tbody td { padding:4px 7px; border-bottom:1px solid #dde5ee; font-size:9px; }
  table.materias tbody td.c { text-align:center; }
  table.totales { width:100%; border-collapse:collapse; background:#eef2f7; border:1px solid #c8d4e0; margin-bottom:14px; }
  table.totales td { padding:4px 8px; font-size:10px; }
  table.totales td.tl { font-weight:bold; color:#1a3a5c; width:30%; }
  table.firmas { width:100%; margin-top:40px; }
  table.firmas td { text-align:center; width:50%; padding:0 20px; }
  .firma-linea { border-top:1px solid #333; width:180px; margin:0 auto 5px; }
</style>
</head>
<body>

<div class="header">
  <div class="inst">Instituto Tecnológico de Matehuala</div>
  <div class="sub">Tecnológico Nacional de México</div>
  <div class="titulo">Carga Académica</div>
</div>

<table class="alumno">
  <tr>
    <td class="lbl">Nombre</td>
    <td colspan="3">{{ $alumno->nombre_completo }}</td>
  </tr>
  <tr>
    <td class="lbl">No. control</td>
    <td>{{ $alumno->numero_control }}</td>
    <td class="lbl">Semestre</td>
    <td>{{ $alumno->semestre_actual }}°</td>
  </tr>
  <tr>
    <td class="lbl">Carrera</td>
    <td colspan="3">{{ $alumno->carrera }}</td>
  </tr>
  <tr>
    <td class="lbl">Periodo</td>
    <td colspan="3">{{ $periodo->nombre_periodo }}</td>
  </tr>
</table>

<table class="materias">
  <thead>
    <tr>
      <th>Clave</th>
      <th>Materia</th>
      <th class="c">Créditos</th>
      <th>Grupo</th>
      <th>Docente</th>
      <th>Aula</th>
      <th>Horario</th>
    </tr>
  </thead>
  <tbody>
    @forelse($materias as $m)
    <tr>
      <td>{{ $m->clave }}</td>
      <td>{{ $m->materia }}</td>
      <td class="c">{{ $m->creditos }}</td>
      <td>{{ $m->clave_grupo }}</td>
      <td>{{ $m->docente }}</td>
      <td>{{ $m->aula ?? '—' }}</td>
      <td>
        @if($m->dia && $m->hora_inicio)
          {{ $m->dia }} {{ $m->hora_inicio }} - {{ $m->hora_fin }}
        @else —
        @endif
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="7" style="text-align:center;padding:10px;color:#888;">Sin materias registradas.</td>
    </tr>
    @endforelse
  </tbody>
</table>

<table class="totales">
  <tr>
    <td class="tl">Total de materias</td>
    <td><strong>{{ $materias->count() }}</strong></td>
    <td class="tl">Total de créditos</td>
    <td><strong>{{ $totalCreditos }}</strong></td>
  </tr>
</table>

<table class="firmas">
  <tr>
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

<table style="width:100%;position:fixed;bottom:16px;left:44px;right:44px;border-top:1px solid #ccc;">
  <tr>
    <td style="font-size:8px;color:#aaa;">Generado por SICE — Sistema Institucional de Control Escolar</td>
    <td style="font-size:8px;color:#aaa;text-align:right;">{{ now()->format('d/m/Y H:i') }}</td>
  </tr>
</table>

</body>
</html>
