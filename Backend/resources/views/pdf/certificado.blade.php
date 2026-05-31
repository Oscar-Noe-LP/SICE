<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  /* =====================================================
     CERTIFICADO DE ESTUDIOS
     Tamaño: Carta horizontal (landscape) → 279 x 216 mm
     ===================================================== */

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Times New Roman', Times, serif;
    background: #ffffff;
    color: #1a1a1a;
    width: 279mm;
    height: 216mm;
    position: relative;
  }

  .marco-exterior {
    position: absolute;
    inset: 6mm;
    border: 3px solid #8B1A1A;
  }
  .marco-interior {
    position: absolute;
    inset: 9mm;
    border: 1px solid #8B1A1A;
  }

  .contenido {
    position: absolute;
    inset: 13mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
  }

  .cabecera {
    width: 100%;
  }
  .tecNM {
    font-size: 11pt;
    font-weight: bold;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #8B1A1A;
  }
  .instituto {
    font-size: 10pt;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #1a1a1a;
    margin-top: 1mm;
  }

  .separador {
    width: 70%;
    height: 2px;
    background: linear-gradient(to right, transparent, #8B1A1A, transparent);
    margin: 3mm auto;
  }

  .titulo {
    font-size: 22pt;
    font-weight: bold;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #8B1A1A;
    margin: 2mm 0;
  }

  .cuerpo {
    width: 80%;
  }
  .texto-normal {
    font-size: 11pt;
    line-height: 1.8;
    color: #1a1a1a;
  }
  .nombre-alumno {
    font-size: 18pt;
    font-weight: bold;
    color: #1a1a1a;
    letter-spacing: 1px;
    margin: 3mm 0;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 1mm;
    display: inline-block;
  }

  .pie {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 4mm;
  }
  .pie-texto {
    font-size: 9pt;
    color: #555;
    text-align: center;
    width: 45%;
  }
  .firma-bloque {
    text-align: center;
    width: 45%;
  }
  .firma-linea {
    border-top: 1px solid #1a1a1a;
    width: 140px;
    margin: 0 auto 2mm;
  }
  .firma-texto {
    font-size: 9pt;
    color: #1a1a1a;
  }

  .sello-agua {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 160px;
    opacity: 0.04;
  }
</style>
</head>
<body>

  <div class="marco-exterior"></div>
  <div class="marco-interior"></div>

  <div class="contenido">

    <div class="cabecera">
      <div class="tecNM">Tecnológico Nacional de México</div>
      <div class="instituto">Instituto Tecnológico de Matehuala</div>
    </div>

    <div class="separador"></div>

    <div class="titulo">Certificado de Estudios</div>

    <div class="cuerpo">
      <p class="texto-normal">El que suscribe, <strong>Director de Servicios Escolares</strong>, hace constar que</p>

      <div class="nombre-alumno">{{ $nombre_completo }}</div>

      <p class="texto-normal">
        con número de control <strong>{{ $numero_control }}</strong>,
        ha cursado satisfactoriamente los estudios correspondientes a la carrera de
        <strong>{{ $carrera }}</strong>, cursando actualmente el <strong>{{ $semestre }}° semestre</strong>,
        con un promedio general de <strong>{{ number_format($promedio_general, 2) }}</strong>.
      </p>

      <p class="texto-normal" style="margin-top:4mm; font-style:italic; font-size:10pt; color:#555;">
        Se expide el presente certificado para los fines que al interesado convengan.
      </p>
    </div>

    <div class="pie">
      <div class="pie-texto">
        Matehuala, S.L.P.<br>
        {{ $fecha_emision->format('d') }} de {{ $fecha_emision->format('F') }} de {{ $fecha_emision->format('Y') }}
      </div>

      <div class="firma-bloque">
        <div class="firma-linea"></div>
        <div class="firma-texto">
          Director de Servicios Escolares<br>
          <em>Sello Digital</em>
        </div>
      </div>
    </div>

  </div>

</body>
</html>
