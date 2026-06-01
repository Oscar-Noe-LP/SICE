<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="kardex-page">

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Kardex</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Consulta de Kardex</h1>
          <p class="page-subtitulo">Ingresa el número de control del alumno para consultar su historial académico completo.</p>
        </div>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="toast.visible" class="toast" :class="toast.tipo">
          <span class="toast-icono">
            <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </span>
          {{ toast.mensaje }}
        </div>
      </transition>

      <!-- ══════════════════════════════════════════ -->
      <!-- BUSCADOR — entrada única, un solo botón   -->
      <!-- ══════════════════════════════════════════ -->
      <div class="buscador-card">
        <label class="buscador-label">Número de Control del Alumno</label>
        <div class="buscador-fila">
          <div class="buscador-input-wrap" :class="{ activo: noControl.length > 0, error: errorAlumno }">
            <svg class="buscador-icono-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              ref="inputNoControl"
              v-model="noControl"
              type="text"
              placeholder="Ej: 21456887"
              class="buscador-input"
              @keyup.enter="consultarKardex"
              @input="errorAlumno = false"
              maxlength="20"
              autocomplete="off"
            />
            <button v-if="noControl" @click="limpiarBusqueda" class="btn-limpiar-input">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                <line x1="18" y1="6" x2="6" y2="18" stroke-width="2.5"/>
                <line x1="6" y1="6" x2="18" y2="18" stroke-width="2.5"/>
              </svg>
            </button>
          </div>
          <button @click="consultarKardex" class="btn-buscar" :disabled="cargando || !noControl.trim()">
            <span v-if="cargando" class="spinner"></span>
            <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            {{ cargando ? 'Consultando...' : 'Consultar Kardex' }}
          </button>
        </div>

        <!-- Hint -->
        <p class="buscador-hint">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" style="flex-shrink:0">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Presiona <kbd>Enter</kbd> o el botón para consultar. El número de control es único por alumno.
        </p>

        <!-- Últimas consultas -->
        <div v-if="ultimasConsultas.length > 0" class="ultimas-consultas">
          <span class="consultas-label">Recientes:</span>
          <button
            v-for="nc in ultimasConsultas"
            :key="nc"
            class="consulta-chip"
            @click="noControl = nc; consultarKardex()"
          >{{ nc }}</button>
        </div>
      </div>

      <!-- Alerta: alumno no encontrado -->
      <transition name="alerta-fade">
        <div v-if="errorAlumno" class="alerta-error">
          <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>No se encontró ningún alumno con el número de control <strong>{{ noControl }}</strong>. Verifica e intenta de nuevo.</span>
        </div>
      </transition>

      <!-- ═══════════════════════════════════════ -->
      <!-- ESTADO: Cargando (skeleton)             -->
      <!-- ═══════════════════════════════════════ -->
      <div v-if="cargando" class="skeleton-wrap">
        <div class="skeleton-card">
          <div class="sk-avatar"></div>
          <div class="sk-lineas">
            <div class="sk-linea sk-ancha"></div>
            <div class="sk-linea sk-media"></div>
            <div class="sk-linea sk-corta"></div>
          </div>
        </div>
        <div class="skeleton-barra-wrap">
          <div class="sk-linea sk-media" style="width:200px"></div>
          <div class="sk-barra"></div>
        </div>
        <div class="skeleton-tabla">
          <div class="sk-thead"></div>
          <div v-for="i in 5" :key="i" class="sk-fila"></div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════ -->
      <!-- RESULTADO: Información completa         -->
      <!-- ═══════════════════════════════════════ -->
      <div v-if="alumno && !cargando" class="resultado-wrap">

        <!-- ── Card perfil del alumno ── -->
        <div class="alumno-card">
          <div class="alumno-foto-wrap">
            <img v-if="alumno.foto" :src="alumno.foto" :alt="alumno.nombre" class="alumno-foto" />
            <div v-else class="alumno-foto-placeholder">
              <span class="avatar-iniciales">{{ iniciales(alumno.nombre) }}</span>
            </div>
          </div>
          <div class="alumno-datos">
            <h2 class="alumno-nombre">{{ alumno.nombre }}</h2>
            <div class="alumno-meta">
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1"/>
                </svg>
                <strong>{{ alumno.no_control }}</strong>
              </span>
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                {{ alumno.carrera }}
              </span>
              <span v-if="alumno.semestre_actual" class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                {{ alumno.semestre_actual }}° Semestre
              </span>
              <span v-if="alumno.estatus" class="meta-item">
                <span class="estatus-badge" :class="claseEstatus(alumno.estatus)">{{ alumno.estatus }}</span>
              </span>
            </div>
            <div v-if="alumno.plan_estudio" class="alumno-plan">Plan: {{ alumno.plan_estudio }}</div>
          </div>
          <div class="alumno-acciones">
            <button @click="irADetalle" class="btn-primario">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
              </svg>
              Kardex completo
            </button>
          </div>
        </div>

        <!-- ── Avance en créditos ── -->
        <div v-if="alumno.creditos_totales" class="creditos-card">
          <div class="creditos-header">
            <span class="creditos-titulo">Avance en Créditos</span>
            <span class="creditos-texto" :class="{ 'creditos-completo': porcentajeCreditos >= 80 }">
              {{ alumno.creditos_acumulados }} / {{ alumno.creditos_totales }} ({{ porcentajeCreditos }}%)
            </span>
          </div>
          <div class="creditos-barra-track">
            <div
              class="creditos-barra-fill"
              :style="{ width: porcentajeCreditos + '%', background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A' }"
            ></div>
          </div>
        </div>

        <!-- ── Resumen rápido ── -->
        <div class="resumen-grid" v-if="resumenKardex.totalMaterias > 0">
          <div class="resumen-item">
            <span class="resumen-num">{{ resumenKardex.totalMaterias }}</span>
            <span class="resumen-label">Total materias</span>
          </div>
          <div class="resumen-item">
            <span class="resumen-num verde">{{ resumenKardex.acreditadas }}</span>
            <span class="resumen-label">Acreditadas</span>
          </div>
          <div class="resumen-item">
            <span class="resumen-num rojo">{{ resumenKardex.reprobadas }}</span>
            <span class="resumen-label">Reprobadas</span>
          </div>
          <div class="resumen-item">
            <span class="resumen-num amarillo">{{ resumenKardex.pendientes }}</span>
            <span class="resumen-label">En curso</span>
          </div>
          <div class="resumen-item">
            <span class="resumen-num azul">{{ resumenKardex.promedio }}</span>
            <span class="resumen-label">Promedio</span>
          </div>
        </div>

        <!-- ── Acordeón de semestres ── -->
        <div class="semestres-seccion">
          <div class="semestres-header">
            <h3 class="seccion-titulo">Materias por Semestre</h3>
            <div class="semestres-controles">
              <button @click="expandirTodo" class="btn-control">Expandir todo</button>
              <button @click="contraerTodo" class="btn-control">Contraer todo</button>
            </div>
          </div>

          <div v-if="kardex.semestres?.length === 0" class="estado-vacio">
            <p>No hay materias registradas en el kardex.</p>
          </div>

          <div v-for="semestre in kardex.semestres" :key="semestre.numero" class="semestre-item">
            <button
              class="semestre-cabecera"
              @click="toggleSemestre(semestre.numero)"
              :class="{ abierto: semestresAbiertos.includes(semestre.numero) }"
            >
              <div class="semestre-izq">
                <span class="semestre-titulo">Semestre {{ semestre.numero }}</span>
                <span class="semestre-conteo">{{ semestre.materias.length }} materias</span>
              </div>
              <div class="semestre-meta">
                <span class="semestre-badge acreditadas">{{ semestre.acreditadas }} acred.</span>
                <span v-if="semestre.reprobadas > 0" class="semestre-badge reprobadas">{{ semestre.reprobadas }} repr.</span>
                <span v-if="semestre.pendientes > 0" class="semestre-badge pendientes">{{ semestre.pendientes }} en curso</span>
                <svg
                  class="semestre-flecha"
                  :class="{ girado: semestresAbiertos.includes(semestre.numero) }"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </button>
            <transition name="semestre-expand">
              <div v-if="semestresAbiertos.includes(semestre.numero)" class="semestre-contenido">
                <table class="tabla-materias">
                  <thead>
                    <tr>
                      <th>Clave</th>
                      <th>Materia</th>
                      <th class="centrado">Créditos</th>
                      <th class="centrado">Calificación</th>
                      <th class="centrado">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="m in semestre.materias"
                      :key="m.clave"
                      :class="{ 'fila-reprobada': m.estado === 'reprobada' }"
                    >
                      <td><span class="clave-chip">{{ m.clave }}</span></td>
                      <td>
                        <div class="materia-nombre-celda">
                          <svg v-if="m.estado === 'reprobada'" fill="none" viewBox="0 0 24 24" stroke="#DC2626" width="14" height="14" class="icono-advertencia">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                          </svg>
                          {{ m.nombre }}
                        </div>
                      </td>
                      <td class="centrado texto-secundario">{{ m.creditos }}</td>
                      <td class="centrado">
                        <strong v-if="m.calificacion !== null" :class="claseCalificacion(m.estado)">{{ m.calificacion }}</strong>
                        <span v-else class="texto-secundario">—</span>
                      </td>
                      <td class="centrado">
                        <span class="badge-estado" :style="estiloBadgeEstado(m.estado)">{{ etiquetaEstado(m.estado) }}</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Estado inicial (sin búsqueda) -->
      <div v-if="!alumno && !cargando && !errorAlumno" class="estado-inicial">
        <div class="estado-inicial-icono">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3>Consulta el kardex de un alumno</h3>
        <p>Ingresa el número de control en el campo de búsqueda y presiona <strong>Enter</strong> o el botón <strong>Consultar</strong>.</p>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`
const router = useRouter()

// ── Estado ────────────────────────────────────────────────────────────
const cargando        = ref(false)
const errorAlumno     = ref(false)
const noControl       = ref('')
const alumno          = ref(null)
const kardex          = ref({ semestres: [] })
const semestresAbiertos = ref([])
const ultimasConsultas  = ref([])
const inputNoControl    = ref(null)

// ── Toast ─────────────────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null

const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Consultar Kardex ──────────────────────────────────────────────────
const consultarKardex = async () => {
  const nc = noControl.value.trim()
  if (!nc) {
    nextTick(() => inputNoControl.value?.focus())
    return
  }
  cargando.value    = true
  errorAlumno.value = false
  alumno.value      = null
  kardex.value      = { semestres: [] }

  try {
    const res = await fetch(`${API}/kardex/${nc}`)
    if (res.status === 404) { errorAlumno.value = true; return }
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()

    alumno.value = data.alumno
    kardex.value = data.kardex ?? { semestres: [] }

    // Guardar en historial de consultas
    if (!ultimasConsultas.value.includes(nc)) {
      ultimasConsultas.value.unshift(nc)
      if (ultimasConsultas.value.length > 5) ultimasConsultas.value.pop()
    }

    // Abrir el primer semestre por defecto
    semestresAbiertos.value = kardex.value.semestres?.length > 0
      ? [kardex.value.semestres[0].numero]
      : []

    mostrarToast(`Kardex de ${data.alumno?.nombre || nc} cargado correctamente`, 'exito')
  } catch (error) {
    console.error('Error consultando kardex:', error)
    mostrarToast('No se pudo conectar con el servidor. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

const limpiarBusqueda = () => {
  noControl.value   = ''
  alumno.value      = null
  errorAlumno.value = false
  kardex.value      = { semestres: [] }
  nextTick(() => inputNoControl.value?.focus())
}

// ── Acordeón ──────────────────────────────────────────────────────────
const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else semestresAbiertos.value.splice(idx, 1)
}
const expandirTodo = () => {
  semestresAbiertos.value = kardex.value.semestres?.map(s => s.numero) ?? []
}
const contraerTodo = () => { semestresAbiertos.value = [] }

// ── Computed ──────────────────────────────────────────────────────────
const porcentajeCreditos = computed(() => {
  if (!alumno.value?.creditos_totales) return 0
  return Math.round((alumno.value.creditos_acumulados / alumno.value.creditos_totales) * 100)
})

const resumenKardex = computed(() => {
  const semestres = kardex.value.semestres ?? []
  let totalMaterias = 0, acreditadas = 0, reprobadas = 0, pendientes = 0, sumaCals = 0, numCals = 0
  for (const sem of semestres) {
    for (const m of sem.materias ?? []) {
      totalMaterias++
      if (m.estado === 'acreditada') { acreditadas++; if (m.calificacion) { sumaCals += m.calificacion; numCals++ } }
      else if (m.estado === 'reprobada') reprobadas++
      else if (m.estado === 'pendiente') pendientes++
    }
  }
  return {
    totalMaterias,
    acreditadas,
    reprobadas,
    pendientes,
    promedio: numCals > 0 ? (sumaCals / numCals).toFixed(1) : '—'
  }
})

// ── Helpers ───────────────────────────────────────────────────────────
const iniciales = (nombre) => {
  if (!nombre) return '?'
  return nombre.split(' ').slice(0, 2).map(p => p[0]).join('').toUpperCase()
}

const claseEstatus = (e) => e?.toLowerCase().replace(/\s/g, '-') ?? ''

const irADetalle = () => router.push(`/kardex/${noControl.value.trim()}`)

const estiloBadgeEstado = (estado) => {
  const estilos = {
    acreditada: { background: '#DCFCE7', color: '#16A34A' },
    reprobada:  { background: '#FEF2F2', color: '#DC2626' },
    pendiente:  { background: '#FEF3C7', color: '#F59E0B' },
    no_cursada: { background: '#F3F4F6', color: '#6B7280' },
  }
  return estilos[estado] ?? estilos.no_cursada
}

const etiquetaEstado = (estado) => {
  const etiquetas = {
    acreditada: 'Acreditada',
    reprobada:  'Reprobada',
    pendiente:  'En curso',
    no_cursada: 'No cursada',
  }
  return etiquetas[estado] ?? estado
}

const claseCalificacion = (estado) => ({
  'calif-aprobada':  estado === 'acreditada',
  'calif-reprobada': estado === 'reprobada',
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.kardex-page {
  width: 100%;
  box-sizing: border-box;
  margin: 0 auto;
  background: #F5F5F5;
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  max-width: 100%;
}

/* Barra de carga */
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: barra-anim 1.4s linear infinite; }
@keyframes barra-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 0.4rem; color: #6B7280; font-size: 0.88rem; margin-bottom: 0.75rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.breadcrumb-actual { color: #1B396A; font-weight: 600; }

/* Header */
.page-header { margin-bottom: 1.4rem; }
.page-title { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; margin: 0 0 4px; }
.page-subtitulo { color: #6B7280; font-size: 0.88rem; margin: 0; }

/* Toast */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 10px; padding: 0.9rem 1.4rem; border-radius: 10px; color: white; font-weight: 700; font-size: 0.9rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px; font-family: 'Montserrat', sans-serif; }
.toast.exito { background: #1B396A; }
.toast.error { background: #DC2626; }
.toast.info  { background: #2563EB; }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; display: flex; align-items: center; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* ── Buscador mejorado ── */
.buscador-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  padding: 1.6rem;
  margin-bottom: 1.5rem;
}
.buscador-label { display: block; font-size: 0.88rem; font-weight: 700; color: #1A1A1A; margin-bottom: 0.75rem; }
.buscador-fila { display: flex; gap: 0.75rem; align-items: stretch; flex-wrap: wrap; }
.buscador-input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 200px;
  background: #F5F5F5;
  border: 1.5px solid #E5E7EB;
  border-radius: 10px;
  padding: 0 14px;
  transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}
.buscador-input-wrap.activo,
.buscador-input-wrap:focus-within {
  border-color: #1B396A;
  background: #EFF6FF;
  box-shadow: 0 0 0 3px #DBEAFE;
}
.buscador-input-wrap.error {
  border-color: #DC2626;
  background: #FEF2F2;
  box-shadow: 0 0 0 3px #FEE2E2;
}
.buscador-icono-svg { width: 18px; height: 18px; stroke: #6B7280; flex-shrink: 0; }
.buscador-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 13px 0;
  font-size: 1rem;
  font-family: 'Montserrat', sans-serif;
  color: #1A1A1A;
  outline: none;
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.05em;
}
.buscador-input::placeholder { color: #9CA3AF; letter-spacing: 0; }
.btn-limpiar-input { background: none; border: none; color: #6B7280; cursor: pointer; display: flex; align-items: center; padding: 2px; stroke: #6B7280; flex-shrink: 0; }

.btn-buscar {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #1B396A;
  color: white;
  border: none;
  padding: 0 22px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.92rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.2s;
  white-space: nowrap;
  min-height: 48px;
}
.btn-buscar:hover:not(:disabled) { background: #1D4ED8; }
.btn-buscar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.buscador-hint {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  color: #6B7280;
  margin-top: 0.6rem;
  margin-bottom: 0;
}
.buscador-hint svg { stroke: #6B7280; }
kbd {
  display: inline-block;
  background: #F3F4F6;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  padding: 1px 5px;
  font-family: monospace;
  font-size: 0.72rem;
  color: #374151;
}

/* Últimas consultas */
.ultimas-consultas { display: flex; align-items: center; gap: 8px; margin-top: 0.75rem; flex-wrap: wrap; }
.consultas-label { font-size: 0.78rem; color: #6B7280; font-weight: 600; }
.consulta-chip { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 20px; padding: 3px 12px; font-size: 0.8rem; color: #1B396A; font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.15s; font-variant-numeric: tabular-nums; }
.consulta-chip:hover { background: #DBEAFE; }

/* Alerta */
.alerta-error { display: flex; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 12px 16px; margin-bottom: 1.4rem; font-size: 0.9rem; color: #DC2626; font-weight: 500; }
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: #DC2626; }
.alerta-fade-enter-active, .alerta-fade-leave-active { transition: all 0.25s ease; }
.alerta-fade-enter-from, .alerta-fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* Estado inicial */
.estado-inicial {
  text-align: center;
  padding: 4rem 2rem;
  color: #6B7280;
}
.estado-inicial-icono {
  width: 80px; height: 80px;
  background: #EFF6FF;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.2rem;
}
.estado-inicial-icono svg { stroke: #1B396A; }
.estado-inicial h3 { font-size: 1.1rem; font-weight: 700; color: #1A1A1A; margin: 0 0 0.5rem; }
.estado-inicial p { font-size: 0.9rem; margin: 0; max-width: 400px; margin: 0 auto; }

/* Skeleton */
.skeleton-wrap { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
.skeleton-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; padding: 1.4rem; display: flex; align-items: center; gap: 1rem; }
.sk-avatar { width: 64px; height: 64px; border-radius: 12px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; flex-shrink:0; }
.sk-lineas { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.sk-linea { height: 14px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-ancha { width: 60%; } .sk-media { width: 40%; } .sk-corta { width: 25%; }
.skeleton-barra-wrap { background:#FFFFFF; border-radius:12px; border:1px solid #E5E7EB; padding:1.2rem 1.4rem; display:flex; flex-direction:column; gap:8px; }
.sk-barra { height: 12px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.skeleton-tabla { background:#FFFFFF; border-radius:12px; border:1px solid #E5E7EB; overflow:hidden; }
.sk-thead { height: 40px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-fila { height: 48px; border-top: 1px solid #E5E7EB; background: linear-gradient(90deg,#F5F5F5 25%,#FFFFFF 50%,#F5F5F5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* ── Card alumno ── */
.resultado-wrap { display: flex; flex-direction: column; gap: 1rem; }
.alumno-card { background: #FFFFFF; border-radius: 14px; border: 1px solid #E5E7EB; box-shadow: 0 4px 14px rgba(0,0,0,0.06); padding: 1.6rem; display: flex; align-items: center; gap: 1.4rem; flex-wrap: wrap; }
.alumno-foto-wrap { flex-shrink: 0; }
.alumno-foto { width: 72px; height: 72px; border-radius: 14px; object-fit: cover; border: 2px solid #E5E7EB; }
.alumno-foto-placeholder {
  width: 72px; height: 72px;
  border-radius: 14px;
  background: #1B396A;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #1B396A;
}
.avatar-iniciales { color: white; font-weight: 700; font-size: 1.3rem; font-family: 'Montserrat', sans-serif; }
.alumno-datos { flex: 1; min-width: 0; }
.alumno-nombre { font-size: 1.2rem; font-weight: 700; color: #1A1A1A; margin: 0 0 0.5rem; }
.alumno-meta { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; margin-bottom: 4px; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6B7280; }
.meta-item svg { stroke: #6B7280; flex-shrink: 0; }
.meta-item strong { color: #1A1A1A; font-variant-numeric: tabular-nums; }
.alumno-plan { font-size: 0.78rem; color: #9CA3AF; }
.alumno-acciones { display: flex; gap: 0.75rem; flex-shrink: 0; flex-wrap: wrap; }

.estatus-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
.estatus-badge.activo          { background: #DCFCE7; color: #16A34A; }
.estatus-badge.baja-temporal   { background: #FEF3C7; color: #F59E0B; }
.estatus-badge.baja-definitiva { background: #FEE2E2; color: #DC2626; }
.estatus-badge.titulado        { background: #EDE9FE; color: #7C3AED; }
.estatus-badge.egresado        { background: #DBEAFE; color: #1B396A; }

/* Créditos */
.creditos-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; padding: 1.2rem 1.4rem; }
.creditos-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 0.5rem; }
.creditos-titulo { font-size: 0.9rem; font-weight: 700; color: #1A1A1A; }
.creditos-texto { font-size: 0.85rem; color: #6B7280; font-weight: 500; }
.creditos-completo { color: #16A34A; font-weight: 700; }
.creditos-barra-track { height: 12px; background: #E5E7EB; border-radius: 6px; overflow: hidden; }
.creditos-barra-fill { height: 100%; border-radius: 6px; transition: width 0.8s ease; }

/* Resumen rápido */
.resumen-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.75rem;
}
.resumen-item {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 12px;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.resumen-num { font-size: 1.5rem; font-weight: 700; color: #1A1A1A; font-variant-numeric: tabular-nums; }
.resumen-num.verde  { color: #16A34A; }
.resumen-num.rojo   { color: #DC2626; }
.resumen-num.amarillo { color: #F59E0B; }
.resumen-num.azul   { color: #1B396A; }
.resumen-label { font-size: 0.72rem; color: #6B7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }

/* Semestres */
.semestres-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem; }
.seccion-titulo { font-size: 1.05rem; font-weight: 700; color: #1A1A1A; margin: 0; }
.semestres-controles { display: flex; gap: 0.5rem; }
.btn-control { background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 6px 12px; font-size: 0.8rem; font-weight: 600; color: #6B7280; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-control:hover { background: #DBEAFE; color: #1B396A; border-color: #1B396A; }

.semestre-item { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; margin-bottom: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.semestre-cabecera { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.4rem; background: none; border: none; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.semestre-cabecera:hover { background: #F5F5F5; }
.semestre-cabecera.abierto { background: #EFF6FF; }
.semestre-izq { display: flex; align-items: center; gap: 10px; }
.semestre-titulo { font-size: 0.95rem; font-weight: 700; color: #1A1A1A; }
.semestre-conteo { font-size: 0.78rem; color: #6B7280; }
.semestre-meta { display: flex; align-items: center; gap: 6px; }
.semestre-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
.semestre-badge.acreditadas { background: #DCFCE7; color: #16A34A; }
.semestre-badge.reprobadas  { background: #FEF2F2; color: #DC2626; }
.semestre-badge.pendientes  { background: #FEF3C7; color: #F59E0B; }
.semestre-flecha { stroke: #6B7280; transition: transform 0.25s; flex-shrink: 0; }
.semestre-flecha.girado { transform: rotate(180deg); }
.semestre-contenido { border-top: 1px solid #E5E7EB; }

/* Tabla materias */
.tabla-materias { width: 100%; border-collapse: collapse; }
.tabla-materias th { background: #F5F5F5; padding: 9px 12px; font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-materias th.centrado { text-align: center; }
.tabla-materias td { padding: 8px 12px; border-bottom: 1px solid #F5F5F5; vertical-align: middle; font-size: 0.875rem; }
.tabla-materias td.centrado { text-align: center; }
.tabla-materias tr:last-child td { border-bottom: none; }
.tabla-materias tr:hover { background: #F9FAFB; }
.tabla-materias tr.fila-reprobada { background: #FEF2F2; }
.clave-chip { font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.materia-nombre-celda { display: flex; align-items: center; gap: 6px; color: #1A1A1A; font-weight: 500; }
.icono-advertencia { flex-shrink: 0; }
.texto-secundario { color: #6B7280; }
.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
.calif-aprobada  { color: #16A34A; }
.calif-reprobada { color: #DC2626; }

.estado-vacio { text-align: center; padding: 2rem; color: #6B7280; font-size: 0.9rem; }

/* Acordeón transition */
.semestre-expand-enter-active { transition: all 0.25s ease; }
.semestre-expand-leave-active { transition: all 0.2s ease; }
.semestre-expand-enter-from, .semestre-expand-leave-to { opacity: 0; transform: translateY(-6px); }

/* Botones */
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.2s; white-space: nowrap; }
.btn-primario:hover:not(:disabled) { background: #1D4ED8; }
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Pie */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; margin-top: 2rem; font-family: 'Montserrat', sans-serif; }

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
  .alumno-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .resumen-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 640px) {
  .kardex-page {
    padding: 0.75rem 0.5rem 2rem;
  }
  
  .buscador-fila {
    flex-direction: column;
  }
  
  .btn-buscar {
    width: 100%;
    justify-content: center;
  }
  
  .resumen-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tabla-materias th,
  .tabla-materias td {
    padding: 8px 6px;
    font-size: 0.78rem;
  }
  
  .semestre-cabecera {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>