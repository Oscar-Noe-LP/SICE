<template>
  <MainLayout>
    <div class="analitica-page">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/servicios-escolares" class="breadcrumb-link">Servicios Escolares</router-link>
        <span class="sep">›</span>
        <span class="activo">Analítica Académica</span>
      </div>

      <!-- Encabezado -->
      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Analítica Académica</h1>
          <p class="subtitulo">Indicadores de rendimiento institucional</p>
        </div>
        <button @click="recargarTodo" class="btn-secundario" :disabled="cargando">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          Actualizar
        </button>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Error de carga -->
      <div v-if="errorCarga" class="alerta-error-catalogos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ errorCarga }}
        <button @click="recargarTodo" class="btn-reintentar">Reintentar</button>
      </div>

      <!-- ============================================================ -->
      <!-- SECCIÓN 1: KPIs GENERALES                                    -->
      <!-- ============================================================ -->
      <div class="seccion-titulo-wrap">
        <h2 class="seccion-titulo">Resumen Institucional</h2>
      </div>

      <div v-if="cargandoResumen" class="kpis-grid">
        <div v-for="i in 6" :key="i" class="kpi-card skeleton"></div>
      </div>

      <div v-else class="kpis-grid">
        <div class="kpi-card">
          <div class="kpi-icono azul">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">{{ resumen.promedio_institucional ?? '—' }}</span>
            <span class="kpi-label">Promedio Institucional</span>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icono verde">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor color-verde">{{ resumen.porcentaje_aprobacion ?? '—' }}%</span>
            <span class="kpi-label">Aprobación</span>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icono rojo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor color-rojo">{{ resumen.porcentaje_reprobacion ?? '—' }}%</span>
            <span class="kpi-label">Reprobación</span>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icono morado">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">{{ resumen.total_alumnos ?? '—' }}</span>
            <span class="kpi-label">Alumnos Activos</span>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icono naranja">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">{{ resumen.total_grupos ?? '—' }}</span>
            <span class="kpi-label">Grupos Activos</span>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icono azul-claro">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
              <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-valor">{{ resumen.total_materias ?? '—' }}</span>
            <span class="kpi-label">Materias Activas</span>
          </div>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- SECCIÓN 2: RENDIMIENTO POR CARRERA                          -->
      <!-- ============================================================ -->
      <div class="seccion-titulo-wrap" style="margin-top: 2rem;">
        <h2 class="seccion-titulo">Rendimiento por Carrera</h2>
      </div>

      <div class="tabla-card">
        <div class="tabla-encabezado">
          <div class="busqueda-control" style="max-width:320px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-lupa">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              v-model="busquedaCarrera"
              type="text"
              placeholder="Buscar carrera..."
              class="input-busqueda-control"
            />
          </div>
          <span class="tabla-contador">{{ carrerasFiltradas.length }} carrera(s)</span>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-califs">
            <thead>
              <tr>
                <th>Carrera</th>
                <th class="centrado">Promedio</th>
                <th class="centrado">Aprobados</th>
                <th class="centrado">Reprobados</th>
                <th class="centrado">Eficiencia</th>
                <th>Barra</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cargandoCarreras">
                <td colspan="6" class="sin-resultados"><p>Cargando datos...</p></td>
              </tr>
              <tr v-else-if="carrerasFiltradas.length === 0">
                <td colspan="6" class="sin-resultados">
                  <div class="sin-resultados-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#E5E7EB" stroke-width="1.5" width="48" height="48">
                      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <p>No se encontraron carreras</p>
                  </div>
                </td>
              </tr>
              <tr v-for="c in carrerasFiltradas" :key="c.id_carrera ?? c.nombre">
                <td>
                  <div class="alumno-info">
                    <div class="carrera-dot" :style="{ background: c.color || '#1B396A' }"></div>
                    <span class="alumno-nombre">{{ c.nombre }}</span>
                  </div>
                </td>
                <td class="centrado">
                  <div class="final-chip" :class="clasePromedio(c.promedio)">
                    {{ c.promedio ?? '—' }}
                  </div>
                </td>
                <td class="centrado">
                  <span class="color-verde font-bold">{{ c.aprobados ?? 0 }}</span>
                </td>
                <td class="centrado">
                  <span class="color-rojo font-bold">{{ c.reprobados ?? 0 }}</span>
                </td>
                <td class="centrado">
                  <span
                    class="badge-estado"
                    :class="claseEficiencia(c.eficiencia ?? c.porcentaje_aprobacion)"
                  >
                    {{ c.eficiencia ?? c.porcentaje_aprobacion ?? '—' }}%
                  </span>
                </td>
                <td style="min-width:120px;">
                  <div class="barra-eficiencia-bg">
                    <div
                      class="barra-eficiencia-fill"
                      :style="{
                        width: Math.min(Number(c.eficiencia ?? c.porcentaje_aprobacion ?? 0), 100) + '%',
                        background: colorEficiencia(c.eficiencia ?? c.porcentaje_aprobacion)
                      }"
                    ></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- SECCIÓN 3: MATERIAS CRÍTICAS                                 -->
      <!-- ============================================================ -->
      <div class="seccion-titulo-wrap" style="margin-top: 2rem;">
        <h2 class="seccion-titulo">Materias Críticas</h2>
        <span class="seccion-badge">Mayor índice de reprobación</span>
      </div>

      <div class="tabla-card">
        <div class="tabla-scroll">
          <table class="tabla-califs">
            <thead>
              <tr>
                <th>#</th>
                <th>Materia</th>
                <th>Carrera</th>
                <th class="centrado">Alumnos</th>
                <th class="centrado">Reprobados</th>
                <th class="centrado">Índice</th>
                <th>Riesgo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cargandoMaterias">
                <td colspan="7" class="sin-resultados"><p>Cargando materias...</p></td>
              </tr>
              <tr v-else-if="materiasCriticas.length === 0">
                <td colspan="7" class="sin-resultados">
                  <div class="sin-resultados-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#E5E7EB" stroke-width="1.5" width="48" height="48">
                      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <p>No hay materias críticas registradas</p>
                  </div>
                </td>
              </tr>
              <tr v-for="(m, idx) in materiasCriticas" :key="m.id_materia ?? m.materia">
                <td>
                  <span class="rank-badge" :class="idx < 3 ? 'rank-top' : ''">{{ idx + 1 }}</span>
                </td>
                <td><span class="alumno-nombre">{{ m.materia }}</span></td>
                <td><span class="carrera-tag">{{ m.carrera ?? '—' }}</span></td>
                <td class="centrado"><strong>{{ m.total_alumnos ?? 0 }}</strong></td>
                <td class="centrado">
                  <span class="color-rojo font-bold">{{ m.reprobados ?? 0 }}</span>
                </td>
                <td class="centrado">
                  <div class="final-chip promedio-bajo">
                    {{ m.indice_reprobacion ?? m.porcentaje ?? '—' }}%
                  </div>
                </td>
                <td style="min-width: 120px;">
                  <div class="barra-eficiencia-bg">
                    <div
                      class="barra-eficiencia-fill"
                      :style="{
                        width: Math.min(Number(m.indice_reprobacion ?? m.porcentaje ?? 0), 100) + '%',
                        background: colorRiesgo(m.indice_reprobacion ?? m.porcentaje)
                      }"
                    ></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
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
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/api/axios'

// --- ESTADO ---
const cargando = ref(false)
const cargandoResumen = ref(false)
const cargandoCarreras = ref(false)
const cargandoMaterias = ref(false)
const errorCarga = ref('')

const resumen = ref({})
const carreras = ref([])
const materiasCriticas = ref([])
const busquedaCarrera = ref('')

// Toast
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// --- COMPUTED ---
const carrerasFiltradas = computed(() => {
  if (!busquedaCarrera.value.trim()) return carreras.value
  const term = busquedaCarrera.value.toLowerCase()
  return carreras.value.filter(c => c.nombre?.toLowerCase().includes(term))
})

// --- HELPERS ---
const clasePromedio = (val) => {
  const n = Number(val)
  if (!val) return 'promedio-sin-calificar'
  if (n >= 90) return 'promedio-excelente'
  if (n >= 70) return 'promedio-bien'
  if (n >= 60) return 'promedio-regular'
  return 'promedio-bajo'
}

const claseEficiencia = (val) => {
  const n = Number(val)
  if (!val) return 'sin-calificar'
  if (n >= 80) return 'excelente'
  if (n >= 60) return 'bien'
  if (n >= 40) return 'regular'
  return 'reprobado'
}

const colorEficiencia = (val) => {
  const n = Number(val ?? 0)
  if (n >= 80) return '#16A34A'
  if (n >= 60) return '#1B396A'
  if (n >= 40) return '#F59E0B'
  return '#DC2626'
}

const colorRiesgo = (val) => {
  const n = Number(val ?? 0)
  if (n >= 60) return '#DC2626'
  if (n >= 40) return '#F59E0B'
  if (n >= 20) return '#1B396A'
  return '#16A34A'
}

// --- API ---
async function cargarResumen() {
  cargandoResumen.value = true
  try {
    const { data } = await api.get('/analitica/resumen')
    resumen.value = data.resumen ?? data
  } catch {
    resumen.value = {}
  } finally {
    cargandoResumen.value = false
  }
}

async function cargarCarreras() {
  cargandoCarreras.value = true
  try {
    const { data } = await api.get('/analitica/carreras')
    carreras.value = data.carreras ?? data
  } catch {
    carreras.value = []
  } finally {
    cargandoCarreras.value = false
  }
}

async function cargarMaterias() {
  cargandoMaterias.value = true
  try {
    const { data } = await api.get('/analitica/materias-criticas')
    materiasCriticas.value = data.materias ?? data
  } catch {
    materiasCriticas.value = []
  } finally {
    cargandoMaterias.value = false
  }
}

async function recargarTodo() {
  cargando.value = true
  errorCarga.value = ''
  try {
    await Promise.all([cargarResumen(), cargarCarreras(), cargarMaterias()])
  } catch {
    errorCarga.value = 'No se pudieron cargar los datos de analítica.'
    mostrarToast('Error al cargar analítica', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => recargarTodo())
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.analitica-page {
  width: 100%;
  max-width: 100%;
  font-family: 'Montserrat', sans-serif;
  box-sizing: border-box;
  padding: 1rem 1rem 2rem;
   text-transform: uppercase; 
}

/* Breadcrumb */
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }

/* Encabezado */
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; font-family: 'Montserrat', sans-serif;}
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; font-family: 'Montserrat', sans-serif;}

/* Barra de carga */
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; width: 100%; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Error */
.alerta-error-catalogos { display: flex; align-items: center; gap: 0.6rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 10px; padding: 0.8rem 1.2rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600; }
.btn-reintentar { margin-left: auto; background: #DC2626; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }

/* Sección título */
.seccion-titulo-wrap { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.seccion-titulo { font-size: 1rem; font-weight: 700; color: #1A1A1A; margin: 0;  font-family: 'Montserrat', sans-serif;}
.seccion-badge { background: #FEF2F2; color: #DC2626; font-size: 0.72rem; font-weight: 600; padding: 2px 10px; border-radius: 12px; border: 1px solid #FECACA; }

/* KPIs */
.kpis-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 0.5rem; }
.kpi-card { background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 14px; padding: 1.2rem 1.4rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: box-shadow 0.2s; }
.kpi-card:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
.kpi-card.skeleton { background: #F5F5F5; min-height: 80px; animation: pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.kpi-icono { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-icono.azul { background: #DBEAFE; color: #1B396A; }
.kpi-icono.verde { background: #DCFCE7; color: #16A34A; }
.kpi-icono.rojo { background: #FEF2F2; color: #DC2626; }
.kpi-icono.morado { background: #EDE9FE; color: #7C3AED; }
.kpi-icono.naranja { background: #FFF7ED; color: #F59E0B; }
.kpi-icono.azul-claro { background: #E0F2FE; color: #0284C7; }
.kpi-info { display: flex; flex-direction: column; }
.kpi-valor { font-size: 1.8rem; font-weight: 800; color: #1A1A1A; line-height: 1.1; }
.kpi-label { font-size: 0.78rem; color: #6B7280; font-weight: 600; margin-top: 4px; }

/* Tabla */
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; width: 100%; box-sizing: border-box; }
.tabla-encabezado { padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; gap: 1rem; flex-wrap: wrap; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; white-space: nowrap; }
.tabla-scroll { overflow-x: auto; width: 100%; }
.tabla-califs { width: 100%; border-collapse: collapse; }
.tabla-califs th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; white-space: nowrap; }
.tabla-califs th.centrado { text-align: center; }
.tabla-califs td { padding: 10px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; font-size: 0.875rem; color: #1A1A1A; }
.tabla-califs td.centrado { text-align: center; }
.tabla-califs tr:hover { background: #F9FAFB; }
.tabla-califs tr:last-child td { border-bottom: none; }

/* Busqueda */
.busqueda-control { display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 8px 0 12px; transition: border-color 0.2s; flex: 1; }
.busqueda-control:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-lupa { color: #6B7280; flex-shrink: 0; }
.input-busqueda-control { border: none; background: transparent; padding: 9px 0; font-size: 0.875rem; font-family: inherit; outline: none; width: 100%; color: #1A1A1A; }

/* Chips y badges */
.final-chip { display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.9rem; }
.promedio-excelente { background: #DCFCE7; color: #16A34A; }
.promedio-bien { background: #DBEAFE; color: #1B396A; }
.promedio-regular { background: #FEF3C7; color: #F59E0B; }
.promedio-bajo { background: #FEF2F2; color: #DC2626; }
.promedio-sin-calificar { background: #F3F4F6; color: #6B7280; }

.badge-estado { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.badge-estado.excelente, .excelente { background: #DCFCE7; color: #16A34A; }
.badge-estado.bien, .bien { background: #DBEAFE; color: #1B396A; }
.badge-estado.regular, .regular { background: #FEF3C7; color: #F59E0B; }
.badge-estado.reprobado, .reprobado { background: #FEF2F2; color: #DC2626; }
.badge-estado.sin-calificar, .sin-calificar { background: #F3F4F6; color: #6B7280; }

/* Alumno info */
.alumno-info { display: flex; align-items: center; gap: 0.6rem; }
.alumno-nombre { font-weight: 600; color: #1A1A1A; }
.carrera-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.carrera-tag { background: #EDE9FE; color: #7C3AED; font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 10px; }

/* Rank */
.rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #F3F4F6; color: #6B7280; font-size: 0.75rem; font-weight: 700; }
.rank-badge.rank-top { background: #FEF3C7; color: #F59E0B; }

/* Barras */
.barra-eficiencia-bg { height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
.barra-eficiencia-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }

/* Colores utilitarios */
.color-verde { color: #16A34A; }
.color-rojo { color: #DC2626; }
.font-bold { font-weight: 700; }

/* Sin resultados */
.sin-resultados { padding: 3rem; text-align: center; }
.sin-resultados-inner { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados-inner p { color: #9CA3AF; font-size: 0.9rem; margin: 0; }

/* Botones */
.btn-secundario { background: #DBEAFE; color: #1B396A; border: none; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; white-space: nowrap; }
.btn-secundario:hover:not(:disabled) { background: #BFDBFE; }
.btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }

/* Toast */
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px; color: #FFFFFF; }
.toast.exito { background: #1B396A; }
.toast.error { background: #DC2626; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }

/* Responsive */
@media (max-width: 900px) {
  .kpis-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .kpis-grid { grid-template-columns: 1fr; }
  .encabezado-seccion { flex-direction: column; gap: 1rem; }
}
</style>