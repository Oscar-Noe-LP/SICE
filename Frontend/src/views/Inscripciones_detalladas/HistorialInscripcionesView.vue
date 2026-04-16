<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="historial-page">

      <!-- ── Header ── -->
      <div class="page-header">
        <h1 class="page-title">Historial de Inscripciones</h1>
        <button v-if="alumnoSeleccionado" class="btn-exportar" @click="exportarHistorial" :disabled="exportando">
          <span v-if="exportando" class="spinner-btn"></span>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="btn-icono">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          {{ exportando ? 'Exportando...' : 'Exportar historial' }}
        </button>
      </div>

      <!-- ── Barra de carga ── -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Toast ── -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ── Buscador de alumno ── -->
      <div class="busqueda-card">
        <div class="seccion-titulo">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="seccion-icono">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span>Buscar Alumno</span>
        </div>
        <div class="seccion-linea"></div>

        <div class="search-inline">
          <div class="search-wrapper-field">
            <svg xmlns="http://www.w3.org/2000/svg" class="search-icono-field" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="busquedaAlumno"
              type="text"
              class="campo-input"
              :class="{ 'input-encontrado': alumnoSeleccionado }"
              placeholder="Número de control o nombre del alumno..."
              @keydown.enter="buscarAlumno"
              @input="alumnoSeleccionado = null; periodos = []"
            >
          </div>
          <button class="btn-buscar" @click="buscarAlumno" :disabled="buscando">
            <span v-if="buscando" class="spinner-btn"></span>
            <span v-else>Consultar historial</span>
          </button>
        </div>

        <!-- Bloque alumno encontrado -->
        <transition name="slide-down">
          <div v-if="alumnoSeleccionado" class="bloque-alumno">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="bloque-icono">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="bloque-info">
              <p class="bloque-nombre">{{ alumnoSeleccionado.nombre }}</p>
              <p class="bloque-detalle">{{ alumnoSeleccionado.carrera }} · {{ alumnoSeleccionado.numero_control }}</p>
            </div>
          </div>
        </transition>
      </div>

      <!-- ── Estado inicial sin búsqueda ── -->
      <div v-if="!alumnoSeleccionado && !buscando" class="inicio-estado">
        <svg xmlns="http://www.w3.org/2000/svg" class="inicio-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <p class="inicio-titulo">Consulta el historial de un alumno</p>
        <p class="inicio-subtitulo">Ingresa el número de control o nombre para ver sus inscripciones por periodo</p>
      </div>

      <!-- ── Historial por periodos ── -->
      <transition name="slide-down">
        <div v-if="alumnoSeleccionado && !buscando" class="historial-contenedor">

          <div v-if="periodos.length === 0" class="estado-vacio">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3>Sin historial</h3>
            <p>No hay inscripciones registradas para este alumno.</p>
          </div>

          <div v-else class="periodos-lista">
            <div v-for="periodo in periodos" :key="periodo.id_inscripcion" class="periodo-card">

              <!-- Header acordeón -->
              <div class="periodo-header" @click="togglePeriodo(periodo.id_inscripcion)">
                <div class="periodo-info">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="periodo-icono">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <div>
                    <p class="periodo-nombre">{{ periodo.nombre_periodo }}</p>
                    <p class="periodo-detalle">
                      {{ periodo.materias.length }} materias ·
                      {{ periodo.materias.filter(m => m.estado === 'acreditada').length }} acreditadas ·
                      {{ periodo.total_creditos }} créditos
                    </p>
                  </div>
                </div>
                <div class="periodo-derecha">
                  <span v-if="periodo.es_activo" class="badge badge-activo-periodo">Periodo activo</span>
                  <span class="estatus-badge" :class="claseEstado(periodo.estado_inscripcion)">{{ etiquetaEstado(periodo.estado_inscripcion) }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                       class="flecha-acordeon" :class="{ 'flecha-abierta': abiertos.includes(periodo.id_inscripcion) }">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>

              <!-- Contenido acordeón -->
              <transition name="acordeon">
                <div v-if="abiertos.includes(periodo.id_inscripcion)" class="periodo-contenido">
                  <div class="tabla-scroll">
                    <table class="tabla-historial">
                      <thead>
                        <tr>
                          <th>Clave</th>
                          <th>Materia</th>
                          <th>Créditos</th>
                          <th>Grupo</th>
                          <th>Calificación</th>
                          <th>Estado</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="mat in periodo.materias" :key="mat.id_detalle" class="hist-fila">
                          <td class="td-clave">{{ mat.clave_materia }}</td>
                          <td class="td-materia">{{ mat.nombre_materia }}</td>
                          <td class="td-center">{{ mat.creditos }}</td>
                          <td class="td-gris">{{ mat.grupo }}</td>
                          <td class="td-center">
                            <span v-if="mat.calificacion !== null && mat.calificacion !== undefined"
                                  class="calificacion" :class="claseCalificacion(mat.calificacion)">
                              {{ mat.calificacion }}
                            </span>
                            <span v-else class="sin-cal">—</span>
                          </td>
                          <td>
                            <span class="estatus-badge" :class="claseEstadoMateria(mat.estado)">{{ etiquetaEstadoMateria(mat.estado) }}</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </transition>

            </div>
          </div>
        </div>
      </transition>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ─────────────────────────────────────────────────────────────────────────────
// BASE URL — el equipo de back solo cambia esta constante
// ─────────────────────────────────────────────────────────────────────────────
const API = 'http://localhost:8000/api'

// ─────────────────────────────────────────────────────────────────────────────
// ESTADOS
// ─────────────────────────────────────────────────────────────────────────────
const busquedaAlumno   = ref('')
const buscando         = ref(false)
const exportando       = ref(false)
const alumnoSeleccionado = ref(null)
const periodos           = ref([])
const abiertos           = ref([])

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ─────────────────────────────────────────────────────────────────────────────
// BUSCAR ALUMNO Y CARGAR HISTORIAL
// ─────────────────────────────────────────────────────────────────────────────

/*
 * GET /api/alumnos/control/:busqueda
 * Busca por número de control (o nombre si el back lo soporta).
 * Respuesta: { id_alumno, numero_control, nombre, carrera, semestre }
 *
 * GET /api/inscripciones/historial/:id_alumno
 * Respuesta (array de periodos):
 * [{
 *   id_inscripcion:      number,
 *   nombre_periodo:      string,
 *   es_activo:           boolean,
 *   estado_inscripcion:  "activa" | "baja" | "cambio_pendiente",
 *   total_creditos:      number,
 *   materias: [{
 *     id_detalle:   number,
 *     clave_materia: string,
 *     nombre_materia: string,
 *     creditos:     number,
 *     grupo:        string,
 *     calificacion: number | null,
 *     estado:       "acreditada" | "reprobada" | "inscrita" | "baja"
 *   }]
 * }]
 * Ordenado por periodo más reciente primero.
 */
const buscarAlumno = async () => {
  const q = (busquedaAlumno.value || '').trim()
  if (!q) return

  buscando.value           = true
  alumnoSeleccionado.value = null
  periodos.value           = []
  abiertos.value           = []

  try {
    // 1. Buscar el alumno
    const resAlumno = await fetch(`${API}/alumnos/control/${encodeURIComponent(q)}`)
    if (!resAlumno.ok) throw new Error('Alumno no encontrado')
    const alumno = await resAlumno.json()
    alumnoSeleccionado.value = alumno
    console.log('✅ Alumno encontrado:', alumno)

    // 2. Cargar su historial
    const resHistorial = await fetch(`${API}/inscripciones/historial/${alumno.id_alumno}`)
    if (!resHistorial.ok) throw new Error(`Error ${resHistorial.status}`)
    const data = await resHistorial.json()
    periodos.value = data
    console.log('✅ Historial cargado:', data.length, 'periodos')

    // Abrir automáticamente el periodo más reciente
    if (data.length > 0) abiertos.value = [data[0].id_inscripcion]
    mostrarNotificacion('Historial cargado correctamente', 'exito')
  } catch (err) {
    console.error('❌ buscarAlumno:', err)
    mostrarNotificacion(`No se encontró ningún alumno con el dato ingresado: ${q}`, 'error')
    alumnoSeleccionado.value = null
    periodos.value           = []
  } finally {
    buscando.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// EXPORTAR HISTORIAL
// GET /api/inscripciones/historial/:id_alumno/exportar
// Respuesta: blob (PDF) o redirect a URL del archivo
// ─────────────────────────────────────────────────────────────────────────────
const exportarHistorial = async () => {
  if (!alumnoSeleccionado.value) return
  exportando.value = true
  try {
    const res = await fetch(`${API}/inscripciones/historial/${alumnoSeleccionado.value.id_alumno}/exportar`)
    if (!res.ok) throw new Error(`Error ${res.status}`)

    // Si el back devuelve un blob (PDF):
    const blob = await res.blob()
    const url  = URL.createObjectURL(blob)
    const a    = document.createElement('a')
    a.href     = url
    a.download = `historial_${alumnoSeleccionado.value.numero_control}.pdf`
    a.click()
    URL.revokeObjectURL(url)
    mostrarNotificacion('Historial exportado correctamente', 'exito')
  } catch (err) {
    console.error('❌ exportarHistorial:', err)
    mostrarNotificacion('No se pudo exportar el historial. Intenta de nuevo.', 'error')
  } finally {
    exportando.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// ACORDEÓN
// ─────────────────────────────────────────────────────────────────────────────
const togglePeriodo = (id) => {
  const idx = abiertos.value.indexOf(id)
  if (idx === -1) abiertos.value.push(id)
  else abiertos.value.splice(idx, 1)
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS BADGE
// ─────────────────────────────────────────────────────────────────────────────
const claseEstado    = (e) => ({ 'badge-activa': e === 'activa', 'badge-baja': e === 'baja', 'badge-cambio': e === 'cambio_pendiente' })
const etiquetaEstado = (e) => ({ activa: 'Activa', baja: 'Baja', cambio_pendiente: 'Cambio pendiente' }[e] || e)

const claseEstadoMateria    = (e) => ({ 'badge-acreditada': e === 'acreditada', 'badge-reprobada': e === 'reprobada', 'badge-inscrita': e === 'inscrita', 'badge-baja-mat': e === 'baja' })
const etiquetaEstadoMateria = (e) => ({ acreditada: 'Acreditada', reprobada: 'Reprobada', inscrita: 'En curso', baja: 'Baja' }[e] || e)

const claseCalificacion = (cal) => {
  if (cal >= 9)  return 'cal-excelente'
  if (cal >= 7)  return 'cal-bueno'
  if (cal >= 6)  return 'cal-regular'
  return 'cal-reprobado'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.historial-page {
  --azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;
  --borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;
  --verde:#16A34A;--rojo:#DC2626;--amarillo:#F59E0B;
  max-width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem;
}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;flex-wrap:wrap;gap:.75rem;}
.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-.02em;margin:0;}
.barra-carga-global{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity .3s;}
.barra-carga-global.visible{opacity:1;}
.barra-progreso{height:100%;width:40%;background:#1B396A;border-radius:2px;animation:deslizar 1.2s ease-in-out infinite;}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.notificacion-ui{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;font-size:.93rem;font-weight:500;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,.1);}
.notificacion-ui.exito{background:#DCFCE7;color:#16A34A;border:1px solid #86EFAC;}
.notificacion-ui.error{background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;}
.notif-icono{width:20px;height:20px;flex-shrink:0;}
.notif-fade-enter-active,.notif-fade-leave-active{transition:all .35s ease;}
.notif-fade-enter-from,.notif-fade-leave-to{opacity:0;transform:translateY(-8px);}
.btn-exportar{display:flex;align-items:center;gap:8px;background:var(--azul-suave);color:var(--azul);border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s;}
.btn-exportar:hover:not(:disabled){background:#BFDBFE;}
.btn-exportar:disabled{opacity:.6;cursor:not-allowed;}
.btn-icono{width:16px;height:16px;}
.busqueda-card{background:#fff;border-radius:14px;padding:1.6rem 2rem;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid var(--borde);margin-bottom:1.25rem;}
.seccion-titulo{display:flex;align-items:center;gap:8px;font-size:1rem;font-weight:700;color:var(--azul);margin-bottom:6px;}
.seccion-icono{width:20px;height:20px;stroke:var(--azul);flex-shrink:0;}
.seccion-linea{height:1px;background:var(--borde);margin-bottom:1.2rem;}
.search-inline{display:flex;gap:8px;align-items:stretch;}
.search-wrapper-field{position:relative;flex:1;}
.search-icono-field{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;stroke:var(--gris);pointer-events:none;}
.campo-input{width:100%;padding:10px 14px 10px 38px;border:1.5px solid var(--borde);border-radius:8px;font-size:.93rem;background:#fff;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box;}
.campo-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px var(--azul-suave);}
.campo-input::placeholder{color:#9CA3AF;}
.campo-input.input-encontrado{border-color:var(--verde);background:#F0FDF4;}
.btn-buscar{background:var(--azul);color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s;white-space:nowrap;display:flex;align-items:center;gap:8px;}
.btn-buscar:hover:not(:disabled){background:var(--azul-hover);}
.btn-buscar:disabled{opacity:.6;cursor:not-allowed;}
.bloque-alumno{display:flex;align-items:center;gap:12px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:10px;padding:12px 16px;margin-top:12px;}
.bloque-icono{width:24px;height:24px;stroke:var(--verde);flex-shrink:0;}
.bloque-info{display:flex;flex-direction:column;gap:2px;}
.bloque-nombre{font-size:.95rem;font-weight:700;color:var(--texto);margin:0;}
.bloque-detalle{font-size:.82rem;color:var(--gris);margin:0;}
.inicio-estado{text-align:center;padding:60px 20px;}
.inicio-icono{width:60px;height:60px;stroke:#9CA3AF;margin-bottom:16px;}
.inicio-titulo{font-size:1rem;font-weight:600;color:var(--gris);margin:0 0 8px;}
.inicio-subtitulo{font-size:.88rem;color:#9CA3AF;margin:0;max-width:360px;margin-left:auto;margin-right:auto;}
.historial-contenedor{display:flex;flex-direction:column;gap:0;}
.periodos-lista{display:flex;flex-direction:column;gap:.75rem;}
.periodo-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid var(--borde);overflow:hidden;}
.periodo-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;cursor:pointer;transition:background .15s;user-select:none;}
.periodo-header:hover{background:#F8FAFC;}
.periodo-info{display:flex;align-items:center;gap:12px;}
.periodo-icono{width:20px;height:20px;stroke:var(--azul);flex-shrink:0;}
.periodo-nombre{font-size:.95rem;font-weight:700;color:var(--texto);margin:0 0 2px;}
.periodo-detalle{font-size:.82rem;color:var(--gris);margin:0;}
.periodo-derecha{display:flex;align-items:center;gap:8px;}
.flecha-acordeon{width:18px;height:18px;stroke:var(--gris);transition:transform .25s;flex-shrink:0;}
.flecha-abierta{transform:rotate(180deg);}
.periodo-contenido{border-top:1px solid var(--borde);}
.acordeon-enter-active,.acordeon-leave-active{transition:all .25s ease;overflow:hidden;}
.acordeon-enter-from,.acordeon-leave-to{opacity:0;max-height:0;}
.acordeon-enter-to,.acordeon-leave-from{opacity:1;max-height:800px;}
.tabla-scroll{overflow-x:auto;}
.tabla-historial{width:100%;border-collapse:collapse;font-size:.88rem;}
.tabla-historial thead tr{background:var(--fondo);}
.tabla-historial th{padding:10px 16px;text-align:left;font-weight:600;font-size:.82rem;color:var(--texto);border-bottom:1px solid var(--borde);white-space:nowrap;}
.hist-fila{border-bottom:1px solid var(--borde);transition:background .15s;}
.hist-fila:last-child{border-bottom:none;}
.hist-fila:hover{background:#F8FAFC;}
.hist-fila td{padding:11px 16px;color:var(--texto);vertical-align:middle;}
.td-clave{font-family:monospace;font-size:.85rem;color:var(--gris);}
.td-materia{font-weight:500;}
.td-center{text-align:center;}
.td-gris{font-size:.85rem;color:var(--gris);}
.calificacion{display:inline-flex;align-items:center;justify-content:center;min-width:42px;height:26px;border-radius:6px;font-weight:700;font-size:.85rem;padding:0 6px;}
.cal-excelente{background:#DCFCE7;color:#16A34A;}
.cal-bueno{background:var(--azul-suave);color:var(--azul);}
.cal-regular{background:#FEF3C7;color:#F59E0B;}
.cal-reprobado{background:#FEE2E2;color:#DC2626;}
.sin-cal{color:#9CA3AF;font-weight:600;}
.estatus-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:700;font-family:'Montserrat',sans-serif;white-space:nowrap;}
.badge-activa{background:#DCFCE7;color:#16A34A;}
.badge-baja{background:#FEE2E2;color:#DC2626;}
.badge-cambio{background:#FEF3C7;color:#F59E0B;}
.badge-acreditada{background:#DCFCE7;color:#16A34A;}
.badge-reprobada{background:#FEE2E2;color:#DC2626;}
.badge-inscrita{background:#FEF3C7;color:#F59E0B;}
.badge-baja-mat{background:#F3F4F6;color:var(--gris);}
.badge-activo-periodo{background:var(--azul-suave);color:var(--azul);font-size:.72rem;}
.estado-vacio{text-align:center;padding:3rem 2rem;}
.icono-vacio{width:56px;height:56px;stroke:#9CA3AF;margin-bottom:12px;}
.estado-vacio h3{font-size:1.2rem;color:var(--texto);margin:0 0 6px;}
.estado-vacio p{font-size:.93rem;color:var(--gris);margin:0;}
.slide-down-enter-active{transition:all .3s ease;}
.slide-down-enter-from{opacity:0;transform:translateY(-10px);}
.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0;}
@keyframes girar{to{transform:rotate(360deg)}}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:.82rem;padding-top:2rem;border-top:1px solid var(--borde);margin-top:1.5rem;font-family:'Montserrat',sans-serif;}
</style>