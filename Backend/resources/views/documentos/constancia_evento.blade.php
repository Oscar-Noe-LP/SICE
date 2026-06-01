<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; padding: 50px 60px; }

  .header { text-align: center; margin-bottom: 30px; }
  .inst-nombre { font-size: 13px; font-weight: bold; text-transform: uppercase; color: #1a3a5c; letter-spacing: 1px; }
  .inst-sub    { font-size: 9px; color: #666; margin-top: 3px; }
  .linea-azul  { border: none; border-top: 3px solid #1a3a5c; margin: 14px 0; }

  .doc-titulo  { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #1a3a5c; letter-spacing: 3px; text-align: center; margin: 24px 0 6px; }
  .doc-sub     { font-size: 11px; color: #555; text-align: center; margin-bottom: 30px; }

  .folio { text-align: right; font-size: 9px; color: #999; margin-bottom: 28px; }

  .cuerpo { font-size: 12px; line-height: 2; text-align: justify; margin-bottom: 30px; }
  .cuerpo .destacado { font-weight: bold; text-transform: uppercase; }

  table.datos { width: 100%; border-collapse: collapse; margin: 20px 0 30px; }
  table.datos td { padding: 6px 10px; border: 1px solid #c8d4e0; font-size: 10.5px; }
  table.datos td.lbl { width: 35%; font-weight: bold; background: #eef2f7; color: #1a3a5c; }

  .expide { font-size: 10.5px; color: #444; text-align: justify; margin-bottom: 50px; line-height: 1.8; }

  table.firmas { width: 100%; margin-top: 60px; }
  table.firmas td { text-align: center; padding: 0 20px; width: 50%; }
  .firma-linea { border-top: 1px solid #333; width: 200px; margin: 0 auto 6px; }
  .firma-nombre { font-weight: bold; font-size: 9.5px; text-transform: uppercase; }
  .firma-cargo  { font-size: 9px; color: #666; }

  .sello-box { text-align: center; margin-top: 20px; }
  .sello-texto { font-size: 8px; color: #bbb; font-style: italic; }

  table.footer-tbl { width: 100%; position: fixed; bottom: 20px; left: 60px; right: 60px; border-top: 1px solid #ddd; }
  table.footer-tbl td { font-size: 8px; color: #bbb; padding-top: 4px; }
</style>
</head>
<body>

<div class="header">
  <div class="inst-nombre">Instituto Tecnológico de Matehuala</div>
  <div class="inst-sub">Tecnológico Nacional de México</div>
</div>

<hr class="linea-azul">

<div class="doc-titulo">Constancia de Participación</div>
<div class="doc-sub">{{ strtoupper($tipo_evento) }}</div>

<div class="folio">Folio: {{ $folio }} &nbsp;|&nbsp; Emisión: {{ $fecha_emision }}</div>

<div class="cuerpo">
  La <span class="destacado">Dirección del Instituto Tecnológico de Matehuala</span>,
  perteneciente al Tecnológico Nacional de México, hace constar que el (la) C.
  <span class="destacado">{{ $alumno->nombre_completo }}</span>,
  con número de control <span class="destacado">{{ $alumno->numero_control }}</span>,
  alumno(a) de la carrera de <span class="destacado">{{ $alumno->carrera }}</span>,
  participó en el evento:
  <br><br>
  <span class="destacado">"{{ strtoupper($evento->nombre_evento) }}"</span>
  <br><br>
  celebrado el día <span class="destacado">{{ \Carbon\Carbon::parse($evento->fecha)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</span>,
  organizado por este Instituto.
</div>

<table class="datos">
  <tr>
    <td class="lbl">Participante</td>
    <td>{{ $alumno->nombre_completo }}</td>
  </tr>
  <tr>
    <td class="lbl">Número de control</td>
    <td>{{ $alumno->numero_control }}</td>
  </tr>
  <tr>
    <td class="lbl">Carrera</td>
    <td>{{ $alumno->carrera }}</td>
  </tr>
  <tr>
    <td class="lbl">Semestre</td>
    <td>{{ $alumno->semestre_actual }}°</td>
  </tr>
  <tr>
    <td class="lbl">Evento</td>
    <td>{{ $evento->nombre_evento }}</td>
  </tr>
  <tr>
    <td class="lbl">Tipo de evento</td>
    <td>{{ $tipo_evento }}</td>
  </tr>
  <tr>
    <td class="lbl">Fecha del evento</td>
    <td>{{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') }}</td>
  </tr>
</table>

<div class="expide">
  La presente constancia se expide a petición del interesado(a), para los fines que estime
  convenientes, en Matehuala, San Luis Potosí, a {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}.
</div>

<table class="firmas">
  <tr>
    <td>
      <div class="firma-linea"></div>
      <div class="firma-nombre">Director(a) de la Institución</div>
      <div class="firma-cargo">Instituto Tecnológico de Matehuala</div>
    </td>
    <td>
      <div class="firma-linea"></div>
      <div class="firma-nombre">Responsable del Evento</div>
      <div class="firma-cargo">Coordinación de Actividades</div>
    </td>
  </tr>
</table>

<table class="footer-tbl">
  <tr>
    <td>Documento generado por SICE — Sistema Institucional de Control Escolar</td>
    <td style="text-align:right;">{{ now()->format('d/m/Y H:i') }}</td>
  </tr>
</table>

</body>
</html>
