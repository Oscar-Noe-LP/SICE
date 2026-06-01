<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'DejaVu Sans',sans-serif; font-size:8.5px; color:#1a1a1a; padding:24px 30px; }
  .header { text-align:center; border-bottom:2px solid #1a3a5c; padding-bottom:10px; margin-bottom:12px; }
  .inst { font-size:11px; font-weight:bold; text-transform:uppercase; color:#1a3a5c; }
  .sub  { font-size:8px; color:#666; margin-top:2px; }
  .titulo { font-size:13px; font-weight:bold; text-transform:uppercase; margin-top:8px; color:#1a3a5c; letter-spacing:2px; }
  table.info { width:100%; border-collapse:collapse; margin-bottom:10px; }
  table.info td { padding:3px 6px; border:1px solid #c8d4e0; font-size:8.5px; }
  table.info td.lbl { background:#eef2f7; font-weight:bold; color:#1a3a5c; width:18%; }
  table.califs { width:100%; border-collapse:collapse; margin-bottom:10px; }
  table.califs thead tr { background:#1a3a5c; color:#fff; }
  table.califs thead th { padding:4px 5px; font-size:8px; font-weight:bold; text-align:left; }
  table.califs thead th.c { text-align:center; }
  table.califs tbody tr:nth-child(even) { background:#f5f8fb; }
  table.califs tbody td { padding:3px 5px; border-bottom:1px solid #dde5ee; font-size:8px; }
  table.califs tbody td.c { text-align:center; }
  .aprobado  { color:#1a6e2e; font-weight:bold; }
  .reprobado { color:#c0392b; font-weight:bold; }
  table.resumen { width:100%; border-collapse:collapse; background:#eef2f7; border:1px solid #c8d4e0; margin-bottom:10px; }
  table.resumen td { padding:4px 8px; font-size:9px; }
  table.resumen td.rl { font-weight:bold; color:#1a3a5c; width:30%; }
  table.firmas { width:100%; margin-top:30px; }
  table.firmas td { text-align:center; width:33%; padding:0 10px; }
  .firma-linea { border-top:1px solid #333; width:160px; margin:0 auto 4px; }
</style>
</head>
<body>

<div class="header">
  <div class="inst">Instituto Tecnológico de Matehuala</div>
  <div class="sub">Tecnológico Nacional de México</div>
  <div class="titulo">Acta de Calificaciones</div>
</div>

<table class="info">
  <tr>
    <td class="lbl">Materia</td>
    <td>{{ $grupo->materia }} ({{ $grupo->clave_materia }})</td>
    <td class="lbl">Grupo</td>
    <td>{{ $grupo->clave_grupo }}</td>
  </tr>
  <tr>
    <td class="lbl">Docente</td>
    <td>{{ $grupo->docente }}</td>
    <td class="lbl">Periodo</td>
    <td>{{ $grupo->periodo }}</td>
  </tr>
  <tr>
    <td class="lbl">Aula</td>
    <td>{{ $grupo->aula ?? '—' }}</td>
    <td class="lbl">Créditos</td>
    <td>{{ $grupo->creditos }}</td>
  </tr>
</table>

<table class="califs">
  <thead>
    <tr>
      <th style="width:12%">No. Control</th>
      <th style="width:32%">Nombre</th>
      <th class="c" style="width:8%">U1</th>
      <th class="c" style="width:8%">U2</th>
      <th class="c" style="width:8%">U3</th>
      <th class="c" style="width:8%">U4</th>
      <th class="c" style="width:8%">U5</th>
      <th class="c" style="width:8%">Final</th>
      <th class="c" style="width:8%">Resultado</th>
    </tr>
  </thead>
  <tbody>
    @foreach($alumnos as $a)
    <tr>
      <td>{{ $a['numero_control'] }}</td>
      <td>{{ $a['nombre'] }}</td>
      <td class="c">{{ $a['u1'] ?? '—' }}</td>
      <td class="c">{{ $a['u2'] ?? '—' }}</td>
      <td class="c">{{ $a['u3'] ?? '—' }}</td>
      <td class="c">{{ $a['u4'] ?? '—' }}</td>
      <td class="c">{{ $a['u5'] ?? '—' }}</td>
      <td class="c"><strong>{{ $a['final'] ?? '—' }}</strong></td>
      <td class="c">
        @if($a['final'] !== null)
          <span class="{{ $a['resultado'] === 'A' ? 'aprobado' : 'reprobado' }}">
            {{ $a['resultado'] === 'A' ? 'Aprobado' : 'Reprobado' }}
          </span>
        @else —
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="resumen">
  <tr>
    <td class="rl">Total inscritos</td>
    <td><strong>{{ count($alumnos) }}</strong></td>
    <td class="rl">Aprobados</td>
    <td><strong class="aprobado">{{ $aprobados }}</strong></td>
    <td class="rl">Reprobados</td>
    <td><strong class="reprobado">{{ $reprobados }}</strong></td>
  </tr>
</table>

<table class="firmas">
  <tr>
    <td>
      <div class="firma-linea"></div>
      <div style="font-weight:bold;font-size:8px;text-transform:uppercase;">Docente</div>
    </td>
    <td>
      <div class="firma-linea"></div>
      <div style="font-weight:bold;font-size:8px;text-transform:uppercase;">Jefe(a) de Servicios Escolares</div>
    </td>
    <td>
      <div class="firma-linea"></div>
      <div style="font-weight:bold;font-size:8px;text-transform:uppercase;">Director(a)</div>
    </td>
  </tr>
</table>

<table style="width:100%;position:fixed;bottom:12px;left:30px;right:30px;border-top:1px solid #ccc;">
  <tr>
    <td style="font-size:7px;color:#aaa;">Generado por SICE</td>
    <td style="font-size:7px;color:#aaa;text-align:right;">{{ now()->format('d/m/Y H:i') }}</td>
  </tr>
</table>

</body>
</html>
