<!-- ============================================= -->
<!-- src/views/KardexView.vue                     -->
<!-- Módulo: Kardex — Búsqueda y consulta         -->
<!-- Tablas: kardex, detalle_kardex, materia,     -->
<!--         plan_materia, plan_estudio, carrera  -->
<!-- ============================================= -->
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
        <h1 class="page-title">Consulta de Kardex</h1>
        <p class="page-subtitulo">Consulta el historial académico completo de un alumno mediante su número de control</p>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ── BUSCADOR PROMINENTE ── -->
      <div class="buscador-card">
        <label class="buscador-label">Número de Control del Alumno</label>
        <div class="buscador-fila">
          <div class="buscador-input-wrap" :class="{ activo: noControl.length > 0 }">
            <svg class="buscador-icono-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              v-model="noControl"
              type="text"
              placeholder="Ej: 21456887"
              class="buscador-input"
              @keyup.enter="consultarKardex"
              maxlength="20"
            />
            <button
              v-if="noControl"
              @click="noControl = ''; alumno = null; errorAlumno = false"
              class="btn-limpiar"
            >
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                <line x1="18" y1="6" x2="6" y2="18" stroke-width="2.5"/>
                <line x1="6" y1="6" x2="18" y2="18" stroke-width="2.5"/>
              </svg>
            </button>
          </div>
          <button @click="consultarKardex" class="btn-consultar" :disabled="cargando || !noControl.trim()">
            <span v-if="cargando" class="spinner"></span>
            <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            {{ cargando ? 'Consultando...' : 'Consultar Kardex' }}
          </button>
        </div>

        <!-- Últimas consultas -->
        <div v-if="ultimasConsultas.length > 0" class="ultimas-consultas">
          <span class="consultas-label">Últimas consultas:</span>
          <button
            v-for="nc in ultimasConsultas"
            :key="nc"
            class="consulta-chip"
            @click="noControl = nc; consultarKardex()"
          >{{ nc }}</button>
        </div>
      </div>

      <!-- ── ALERTA: ALUMNO NO ENCONTRADO ── -->
      <div v-if="errorAlumno" class="alerta-error">
        <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>No se encontró ningún alumno con el número de control <strong>{{ noControl }}</strong>. Verifica e intenta de nuevo.</span>
      </div>

      <!-- ── SKELETON DE CARGA ── -->
      <div v-if="cargando" class="skeleton-wrap">
        <!-- Tarjeta alumno skeleton -->
        <div class="skeleton-card">
          <div class="sk-avatar"></div>
          <div class="sk-lineas">
            <div class="sk-linea sk-ancha"></div>
            <div class="sk-linea sk-media"></div>
            <div class="sk-linea sk-corta"></div>
          </div>
        </div>
        <!-- Barra progreso skeleton -->
        <div class="skeleton-barra-wrap">
          <div class="sk-linea sk-media" style="width:180px"></div>
          <div class="sk-barra"></div>
        </div>
        <!-- Tabla skeleton -->
        <div class="skeleton-tabla">
          <div class="sk-thead"></div>
          <div v-for="i in 5" :key="i" class="sk-fila"></div>
        </div>
      </div>

      <!-- ── PANEL COMPLETO DE KARDEX (cuando hay alumno) ── -->
      <div v-if="alumno && !cargando">

        <!-- Card alumno -->
        <div class="alumno-card">
          <div class="alumno-foto-wrap">
            <img v-if="alumno.foto" :src="alumno.foto" :alt="alumno.nombre" class="alumno-foto" />
            <div v-else class="alumno-foto-placeholder">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>
          <div class="alumno-datos">
            <h2 class="alumno-nombre">{{ alumno.nombre }}</h2>
            <div class="alumno-meta">
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1"/>
                </svg>
                No. Control: <strong>{{ alumno.no_control }}</strong>
              </span>
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                {{ alumno.carrera }}
              </span>
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                Plan: {{ alumno.plan_estudio }}
              </span>
            </div>
          </div>
          <div class="alumno-acciones">
            <button @click="irADetalle" class="btn-primario">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
              </svg>
              Ver Kardex Completo
            </button>
            <button @click="irAAvance" class="btn-secundario">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Ver Avance Curricular
            </button>
          </div>
        </div>

        <!-- Barra de progreso de créditos -->
        <div class="creditos-card">
          <div class="creditos-header">
            <span class="creditos-titulo">Avance en Créditos</span>
            <span class="creditos-texto" :class="{ 'creditos-completo': porcentajeCreditos >= 80 }">
              {{ alumno.creditos_acumulados }} de {{ alumno.creditos_totales }} créditos completados ({{ porcentajeCreditos }}%)
            </span>
          </div>
          <div class="creditos-barra-track">
            <div
              class="creditos-barra-fill"
              :style="{
                width: porcentajeCreditos + '%',
                background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A'
              }"
            ></div>
          </div>
        </div>

        <!-- Acordeón de semestres -->
        <div class="semestres-seccion">
          <h3 class="seccion-titulo">Materias por Semestre</h3>
          <div
            v-for="semestre in kardex.semestres"
            :key="semestre.numero"
            class="semestre-item"
          >
            <button
              class="semestre-cabecera"
              @click="toggleSemestre(semestre.numero)"
              :class="{ abierto: semestresAbiertos.includes(semestre.numero) }"
            >
              <span class="semestre-titulo">Semestre {{ semestre.numero }}</span>
              <div class="semestre-meta">
                <span class="semestre-badge acreditadas">{{ semestre.acreditadas }} acreditadas</span>
                <span v-if="semestre.reprobadas > 0" class="semestre-badge reprobadas">{{ semestre.reprobadas }} reprobadas</span>
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
                    <tr v-for="m in semestre.materias" :key="m.clave">
                      <td class="clave-chip">{{ m.clave }}</td>
                      <td>
                        <div class="materia-nombre-celda">
                          <svg v-if="m.estado === 'reprobada'"
                            fill="none" viewBox="0 0 24 24" stroke="#DC2626" width="15" height="15" class="icono-advertencia">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                          </svg>
                          {{ m.nombre }}
                        </div>
                      </td>
                      <td class="centrado texto-secundario">{{ m.creditos }}</td>
                      <td class="centrado">
                        <strong v-if="m.calificacion !== null" :class="claseCalificacion(m.estado)">
                          {{ m.calificacion }}
                        </strong>
                        <span v-else class="texto-secundario">—</span>
                      </td>
                      <td class="centrado">
                        <span class="badge-estado" :style="estiloBadgeEstado(m.estado)">
                          {{ etiquetaEstado(m.estado) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </transition>
          </div>
        </div>

      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()

// ── Estado ──────────────────────────────────────────────────
const cargando      = ref(false)
const errorAlumno   = ref(false)
const noControl     = ref('')
const alumno        = ref(null)
const kardex        = ref({ semestres: [] })
const semestresAbiertos = ref([1]) // primer semestre abierto por defecto
const ultimasConsultas  = ref([])  // solo en memoria de sesión

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Consultar Kardex ─────────────────────────────────────────
const consultarKardex = async () => {
  const nc = noControl.value.trim()
  if (!nc) return
  cargando.value    = true
  errorAlumno.value = false
  alumno.value      = null
  kardex.value      = { semestres: [] }

  try {
    const res = await fetch(`${API}/kardex/${nc}`)
    if (res.status === 404) {
      errorAlumno.value = true
      return
    }
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()

    alumno.value = data.alumno
    kardex.value = data.kardex ?? { semestres: [] }

    // Guardar en historial de sesión (máximo 5, sin repetidos)
    if (!ultimasConsultas.value.includes(nc)) {
      ultimasConsultas.value.unshift(nc)
      if (ultimasConsultas.value.length > 5) ultimasConsultas.value.pop()
    }
    // Abrir primer semestre por defecto
    semestresAbiertos.value = kardex.value.semestres.length > 0
      ? [kardex.value.semestres[0].numero]
      : []

  } catch (error) {
    console.error('Error consultando kardex:', error)
    mostrarNotificacion('No se pudo conectar con el servidor. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Acordeón ─────────────────────────────────────────────────
const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else semestresAbiertos.value.splice(idx, 1)
}

// ── Computed ─────────────────────────────────────────────────
const porcentajeCreditos = computed(() => {
  if (!alumno.value?.creditos_totales) return 0
  return Math.round((alumno.value.creditos_acumulados / alumno.value.creditos_totales) * 100)
})

// ── Navegación ────────────────────────────────────────────────
const irADetalle = () => router.push(`/kardex/${noControl.value.trim()}`)
const irAAvance  = () => router.push(`/kardex/avance?no_control=${noControl.value.trim()}`)

// ── Helpers de badges ────────────────────────────────────────
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
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --azul-suave: #DBEAFE;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --verde:      #16A34A;
  --rojo:       #DC2626;
  --amarillo:   #F59E0B;

  width: 100%;
  box-sizing: border-box;
  margin: 0 auto;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  position: relative;
}

/* BARRA DE CARGA */
.barra-carga {
  position: fixed; top: 74px; left: 0; right: 0; height: 3px;
  z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s;
}
.barra-carga.activa { opacity: 1; }
.barra-progreso {
  height: 100%;
  background: linear-gradient(90deg, var(--azul), var(--azul-hover), var(--azul));
  background-size: 200% 100%;
  animation: barra-anim 1.4s linear infinite;
}
@keyframes barra-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* BREADCRUMB */
.breadcrumb {
  display: flex; align-items: center; gap: 0.4rem;
  color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem;
}
.breadcrumb .sep { color: var(--borde); }
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

/* ENCABEZADO */
.page-header    { margin-bottom: 1.4rem; }
.page-title     { color: var(--texto); font-size: 1.75rem; font-weight: 700; margin: 0 0 4px; }
.page-subtitulo { color: var(--gris); font-size: 0.88rem; margin: 0; }

/* TOAST */
.toast {
  position: fixed; bottom: 2rem; right: 2rem;
  display: flex; align-items: center; gap: 10px; padding: 0.9rem 1.4rem;
  border-radius: 10px; color: white; font-weight: 700; font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px;
  font-family: 'Montserrat', sans-serif;
}
.toast.exito { background: var(--azul); }
.toast.error { background: var(--rojo); }
.toast-icono  { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* BUSCADOR */
.buscador-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid var(--borde);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.6rem; margin-bottom: 1.5rem;
}
.buscador-label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--texto); margin-bottom: 0.6rem; }
.buscador-fila  { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

.buscador-input-wrap {
  display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;
  background: var(--fondo); border: 1.5px solid var(--borde); border-radius: 10px; padding: 0 14px;
  transition: border-color 0.2s, background 0.2s;
}
.buscador-input-wrap.activo,
.buscador-input-wrap:focus-within { border-color: var(--azul); background: var(--azul-suave); }
.buscador-icono-svg { width: 18px; height: 18px; stroke: var(--gris); flex-shrink: 0; }
.buscador-input {
  flex: 1; border: none; background: transparent; padding: 12px 0;
  font-size: 1rem; font-family: inherit; color: var(--texto); outline: none;
}
.buscador-input::placeholder { color: #9CA3AF; }
.btn-limpiar {
  background: none; border: none; color: var(--gris); cursor: pointer;
  display: flex; align-items: center; padding: 2px; stroke: var(--gris);
}

.btn-consultar {
  background: var(--azul); color: #FFFFFF; border: none;
  padding: 12px 1.6rem; border-radius: 10px; font-weight: 600; font-size: 0.95rem;
  display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: inherit;
  transition: background 0.2s; white-space: nowrap;
}
.btn-consultar:hover:not(:disabled) { background: var(--azul-hover); }
.btn-consultar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ÚLTIMAS CONSULTAS */
.ultimas-consultas {
  display: flex; align-items: center; gap: 8px; margin-top: 0.75rem; flex-wrap: wrap;
}
.consultas-label { font-size: 0.8rem; color: var(--gris); }
.consulta-chip {
  background: var(--fondo); border: 1px solid var(--borde); border-radius: 20px;
  padding: 3px 12px; font-size: 0.8rem; color: var(--azul); font-weight: 600;
  cursor: pointer; font-family: inherit; transition: background 0.15s;
}
.consulta-chip:hover { background: var(--azul-suave); }

/* ALERTA ERROR */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px;
  padding: 12px 16px; margin-bottom: 1.4rem;
  font-size: 0.9rem; color: var(--rojo); font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: var(--rojo); }

/* SKELETON */
.skeleton-wrap { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
.skeleton-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid var(--borde);
  padding: 1.4rem; display: flex; align-items: center; gap: 1rem;
}
.sk-avatar { width: 64px; height: 64px; border-radius: 12px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; flex-shrink:0; }
.sk-lineas { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.sk-linea   { height: 14px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-ancha   { width: 60%; }
.sk-media   { width: 40%; }
.sk-corta   { width: 25%; }

.skeleton-barra-wrap { background:#FFFFFF; border-radius:12px; border:1px solid var(--borde); padding:1.2rem 1.4rem; display:flex; flex-direction:column; gap:8px; }
.sk-barra { height: 12px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }

.skeleton-tabla { background:#FFFFFF; border-radius:12px; border:1px solid var(--borde); overflow:hidden; }
.sk-thead { height: 40px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-fila  { height: 48px; border-top: 1px solid var(--borde); background: linear-gradient(90deg,#F9FAFB 25%,#FFFFFF 50%,#F9FAFB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* CARD ALUMNO */
.alumno-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid var(--borde);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.6rem;
  display: flex; align-items: center; gap: 1.4rem; margin-bottom: 1rem; flex-wrap: wrap;
}
.alumno-foto-wrap { flex-shrink: 0; }
.alumno-foto {
  width: 72px; height: 72px; border-radius: 12px; object-fit: cover;
  border: 2px solid var(--borde);
}
.alumno-foto-placeholder {
  width: 72px; height: 72px; border-radius: 12px;
  background: var(--azul-suave); display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--borde);
}
.alumno-foto-placeholder svg { stroke: var(--azul); }
.alumno-datos   { flex: 1; min-width: 0; }
.alumno-nombre  { font-size: 1.2rem; font-weight: 700; color: var(--texto); margin: 0 0 0.5rem; }
.alumno-meta    { display: flex; gap: 1.2rem; flex-wrap: wrap; }
.meta-item      { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: var(--gris); }
.meta-item svg  { stroke: var(--gris); flex-shrink: 0; }
.meta-item strong { color: var(--texto); }

.alumno-acciones { display: flex; gap: 0.75rem; flex-shrink: 0; flex-wrap: wrap; }
.btn-primario {
  background: var(--azul); color: #FFFFFF; border: none;
  padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit;
  transition: background 0.2s;
}
.btn-primario:hover { background: var(--azul-hover); }
.btn-secundario {
  background: var(--azul-suave); color: var(--azul); border: none;
  padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit;
  transition: background 0.2s;
}
.btn-secundario:hover { background: #bfdbfe; }

/* CRÉDITOS */
.creditos-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid var(--borde);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem; margin-bottom: 1.5rem;
}
.creditos-header  { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 0.5rem; }
.creditos-titulo  { font-size: 0.9rem; font-weight: 700; color: var(--texto); }
.creditos-texto   { font-size: 0.85rem; color: var(--gris); font-weight: 500; }
.creditos-completo { color: var(--verde); font-weight: 700; }
.creditos-barra-track { height: 12px; background: var(--borde); border-radius: 6px; overflow: hidden; }
.creditos-barra-fill  { height: 100%; border-radius: 6px; transition: width 0.8s ease; }

/* ACORDEÓN DE SEMESTRES */
.semestres-seccion  { margin-bottom: 1.5rem; }
.seccion-titulo { font-size: 1.05rem; font-weight: 700; color: var(--texto); margin: 0 0 1rem; }

.semestre-item { background: #FFFFFF; border-radius: 12px; border: 1px solid var(--borde); margin-bottom: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.semestre-cabecera {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 1.4rem; background: none; border: none; cursor: pointer;
  font-family: inherit; transition: background 0.15s;
}
.semestre-cabecera:hover  { background: #F9FAFB; }
.semestre-cabecera.abierto { background: var(--azul-suave); }
.semestre-titulo { font-size: 0.95rem; font-weight: 700; color: var(--texto); }
.semestre-meta   { display: flex; align-items: center; gap: 8px; }
.semestre-badge  { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.semestre-badge.acreditadas { background: #DCFCE7; color: var(--verde); }
.semestre-badge.reprobadas  { background: #FEF2F2; color: var(--rojo); }
.semestre-flecha { stroke: var(--gris); transition: transform 0.25s; flex-shrink: 0; }
.semestre-flecha.girado { transform: rotate(180deg); }

.semestre-contenido { border-top: 1px solid var(--borde); }

/* TABLA MATERIAS */
.tabla-materias { width: 100%; border-collapse: collapse; }
.tabla-materias th {
  background: #F9FAFB; padding: 9px 14px; font-size: 0.77rem; font-weight: 700;
  color: var(--gris); text-transform: uppercase; letter-spacing: 0.04em;
  border-bottom: 1px solid var(--borde); text-align: left;
}
.tabla-materias th.centrado { text-align: center; }
.tabla-materias td { padding: 10px 14px; border-bottom: 1px solid #F5F5F5; vertical-align: middle; font-size: 0.875rem; }
.tabla-materias td.centrado { text-align: center; }
.tabla-materias tr:last-child td { border-bottom: none; }
.tabla-materias tr:hover { background: #F9FAFB; }

.clave-chip { font-family: monospace; font-size: 0.82rem; font-weight: 700; color: var(--texto); }
.materia-nombre-celda { display: flex; align-items: center; gap: 6px; color: var(--texto); font-weight: 500; }
.icono-advertencia { flex-shrink: 0; }
.texto-secundario { color: var(--gris); }

.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.calif-aprobada  { color: var(--verde); }
.calif-reprobada { color: var(--rojo); }

/* ANIMACIÓN ACORDEÓN */
.semestre-expand-enter-active { transition: all 0.25s ease; }
.semestre-expand-leave-active { transition: all 0.2s ease; }
.semestre-expand-enter-from, .semestre-expand-leave-to { opacity: 0; transform: translateY(-6px); }

/* PIE */
.pie-pagina {
  text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem;
  border-top: 1px solid var(--borde); margin-top: 1rem;
}
</style>