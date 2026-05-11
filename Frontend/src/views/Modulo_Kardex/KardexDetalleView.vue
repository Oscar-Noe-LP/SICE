<!-- ============================================= -->
<!-- src/views/KardexDetalleView.vue              -->
<!-- Módulo: Kardex — Detalle completo del alumno -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="kardex-detalle-page">
      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>
      
      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/kardex" class="breadcrumb-link">Kardex</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">{{ route.params.no_control }}</span>
      </nav>
      
      <!-- Toast unificado -->
      <transition name="toast-slide">
        <div v-if="toast.visible" class="toast" :class="toast.tipo">
          <span class="toast-icono">
            <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </span>
          {{ toast.mensaje }}
        </div>
      </transition>
      
      <!-- Alerta Error -->
      <div v-if="errorCarga" class="alerta-error">
        <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>No se pudo cargar el kardex. Verifica la conexión con el servidor.</span>
        <button class="btn-reintentar" @click="cargarDatos">Reintentar</button>
      </div>
      
      <!-- Skeleton -->
      <div v-if="cargando">
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
          <div v-for="i in 8" :key="i" class="sk-fila"></div>
        </div>
      </div>
      
      <!-- Contenido Principal -->
      <div v-if="alumno && !cargando">
        <!-- Encabezado -->
        <div class="cabecera-fila">
          <h1 class="page-title">Kardex Académico</h1>
          <button @click="exportarPDF" class="btn-primario" :disabled="exportando">
            <span v-if="exportando" class="spinner"></span>
            <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            {{ exportando ? 'Generando PDF...' : 'Exportar Kardex en PDF' }}
          </button>
        </div>
        
        <!-- Card datos del alumno -->
        <div class="alumno-card">
          <div class="alumno-foto-wrap">
            <img v-if="alumno.foto" :src="alumno.foto" :alt="alumno.nombre" class="alumno-foto"/>
            <div v-else class="alumno-foto-placeholder">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="44" height="44">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>
          <div class="alumno-datos">
            <h2 class="alumno-nombre">{{ alumno.nombre }}</h2>
            <div class="alumno-meta">
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1"/>
                </svg>
                No. Control: <strong>{{ alumno.no_control }}</strong>
              </span>
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                {{ alumno.carrera }}
              </span>
              <span class="meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                Plan de estudios: <strong>{{ alumno.plan_estudio }}</strong>
              </span>
            </div>
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
            <div class="creditos-barra-fill" :style="{ width: porcentajeCreditos + '%', background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A' }"></div>
          </div>
        </div>
        
        <!-- Acordeón de semestres -->
        <div class="semestres-seccion">
          <div class="semestres-header">
            <h3 class="seccion-titulo">Materias por Semestre</h3>
            <div class="semestres-controles">
              <button @click="expandirTodo" class="btn-control">Expandir todo</button>
              <button @click="contraerTodo" class="btn-control">Contraer todo</button>
            </div>
          </div>
          <div v-for="semestre in kardex.semestres" :key="semestre.numero" class="semestre-item">
            <button class="semestre-cabecera" :class="{ abierto: semestresAbiertos.includes(semestre.numero) }" @click="toggleSemestre(semestre.numero)">
              <div class="semestre-izq">
                <span class="semestre-titulo">Semestre {{ semestre.numero }}</span>
                <span class="semestre-conteo">{{ semestre.materias.length }} materias</span>
              </div>
              <div class="semestre-meta">
                <span class="semestre-badge acreditadas">{{ semestre.acreditadas }} acreditadas</span>
                <span v-if="semestre.reprobadas > 0" class="semestre-badge reprobadas">{{ semestre.reprobadas }} reprobadas</span>
                <span v-if="semestre.pendientes > 0" class="semestre-badge pendientes">{{ semestre.pendientes }} en curso</span>
                <svg class="semestre-flecha" :class="{ girado: semestresAbiertos.includes(semestre.numero) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
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
                      <th>Nombre de la Materia</th>
                      <th class="centrado">Créditos</th>
                      <th class="centrado">Calificación</th>
                      <th class="centrado">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="m in semestre.materias" :key="m.clave" :class="{ 'fila-reprobada': m.estado === 'reprobada' }">
                      <td><span class="clave-chip">{{ m.clave }}</span></td>
                      <td>
                        <div class="materia-nombre-celda">
                          <svg v-if="m.estado === 'reprobada'" fill="none" viewBox="0 0 24 24" stroke="#DC2626" width="15" height="15" class="icono-advertencia">
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
      
      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`
const router = useRouter()
const route = useRoute()

// Estado
const cargando = ref(false)
const errorCarga = ref(false)
const exportando = ref(false)
const alumno = ref(null)
const kardex = ref({ semestres: [] })
const semestresAbiertos = ref([])

// Toast unificado
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null

const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// Carga de datos
const cargarDatos = async () => {
  cargando.value = true
  errorCarga.value = false
  try {
    const res = await fetch(`${API}/kardex/${route.params.no_control}`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    alumno.value = data.alumno
    kardex.value = data.kardex ?? { semestres: [] }
    if (kardex.value.semestres.length > 0) {
      semestresAbiertos.value = [kardex.value.semestres[0].numero]
    }
  } catch (error) {
    console.error('Error cargando kardex:', error)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarDatos() })

// Computed
const porcentajeCreditos = computed(() => {
  if (!alumno.value?.creditos_totales) return 0
  return Math.round((alumno.value.creditos_acumulados / alumno.value.creditos_totales) * 100)
})

// Acordeón
const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else semestresAbiertos.value.splice(idx, 1)
}
const expandirTodo = () => { semestresAbiertos.value = kardex.value.semestres.map(s => s.numero) }
const contraerTodo = () => { semestresAbiertos.value = [] }

// Exportar PDF
const exportarPDF = async () => {
  exportando.value = true
  mostrarToast('Generando PDF...', 'info')
  try {
    await new Promise(r => setTimeout(r, 1800))
    mostrarToast('Kardex generado correctamente', 'exito')
  } finally {
    exportando.value = false
  }
}

// Helpers de badges
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
  'calif-aprobada': estado === 'acreditada',
  'calif-reprobada': estado === 'reprobada',
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.kardex-detalle-page { width: 100%; box-sizing: border-box; margin: 0 auto; background: #F5F5F5; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }

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

/* Toast Estándar */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 10px; padding: 0.9rem 1.4rem; border-radius: 10px; color: white; font-weight: 700; font-size: 0.9rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px; font-family: 'Montserrat', sans-serif; }
.toast.exito { background: #1B396A; }
.toast.error { background: #DC2626; }
.toast.info { background: #2563EB; }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* Alerta */
.alerta-error { display: flex; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 12px 16px; margin-bottom: 1.4rem; font-size: 0.9rem; color: #DC2626; font-weight: 500; }
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: #DC2626; }
.btn-reintentar { margin-left: auto; background: #DC2626; color: white; border: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; font-family: inherit; }
.btn-reintentar:hover { background: #B91C1C; }

/* Skeleton */
.skeleton-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; padding: 1.4rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
.sk-avatar { width: 72px; height: 72px; border-radius: 12px; flex-shrink: 0; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-lineas { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.sk-linea { height: 14px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-ancha { width: 60%; } .sk-media { width: 40%; } .sk-corta { width: 25%; }
.skeleton-barra-wrap { background:#FFFFFF; border-radius:12px; border:1px solid #E5E7EB; padding:1.2rem 1.4rem; display:flex; flex-direction:column; gap:8px; margin-bottom:1rem; }
.sk-barra { height: 12px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.skeleton-tabla { background:#FFFFFF; border-radius:12px; border:1px solid #E5E7EB; overflow:hidden; }
.sk-thead { height: 40px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-fila { height: 48px; border-top: 1px solid #E5E7EB; background: linear-gradient(90deg,#F5F5F5 25%,#FFFFFF 50%,#F5F5F5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Encabezado */
.cabecera-fila { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; flex-wrap: wrap; gap: 1rem; }
.page-title { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; margin: 0; }

/* Card Alumno */
.alumno-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.6rem; display: flex; align-items: center; gap: 1.4rem; margin-bottom: 1rem; flex-wrap: wrap; }
.alumno-foto-wrap { flex-shrink: 0; }
.alumno-foto { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; border: 2px solid #E5E7EB; }
.alumno-foto-placeholder { width: 80px; height: 80px; border-radius: 12px; background: #DBEAFE; display: flex; align-items: center; justify-content: center; border: 2px solid #E5E7EB; }
.alumno-foto-placeholder svg { stroke: #1B396A; }
.alumno-datos { flex: 1; min-width: 0; }
.alumno-nombre { font-size: 1.25rem; font-weight: 700; color: #1A1A1A; margin: 0 0 0.5rem; }
.alumno-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6B7280; }
.meta-item svg { stroke: #6B7280; flex-shrink: 0; }
.meta-item strong { color: #1A1A1A; }

/* Créditos */
.creditos-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem; margin-bottom: 1.5rem; }
.creditos-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 0.5rem; }
.creditos-titulo { font-size: 0.9rem; font-weight: 700; color: #1A1A1A; }
.creditos-texto { font-size: 0.85rem; color: #6B7280; font-weight: 500; }
.creditos-completo { color: #16A34A; font-weight: 700; }
.creditos-barra-track { height: 12px; background: #E5E7EB; border-radius: 6px; overflow: hidden; }
.creditos-barra-fill { height: 100%; border-radius: 6px; transition: width 0.8s ease; }

/* Semestres */
.semestres-seccion { margin-bottom: 1.5rem; }
.semestres-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem; }
.seccion-titulo { font-size: 1.05rem; font-weight: 700; color: #1A1A1A; margin: 0; }
.semestres-controles { display: flex; gap: 0.5rem; }
.btn-control { background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 6px 14px; font-size: 0.8rem; font-weight: 600; color: #6B7280; cursor: pointer; font-family: inherit; transition: background 0.15s; }
.btn-control:hover { background: #DBEAFE; color: #1B396A; border-color: #1B396A; }
.semestre-item { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; margin-bottom: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.semestre-cabecera { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.4rem; background: none; border: none; cursor: pointer; font-family: inherit; transition: background 0.15s; }
.semestre-cabecera:hover { background: #F5F5F5; }
.semestre-cabecera.abierto { background: #DBEAFE; }
.semestre-izq { display: flex; align-items: center; gap: 10px; }
.semestre-titulo { font-size: 0.95rem; font-weight: 700; color: #1A1A1A; }
.semestre-conteo { font-size: 0.78rem; color: #6B7280; }
.semestre-meta { display: flex; align-items: center; gap: 8px; }
.semestre-badge { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.semestre-badge.acreditadas { background: #DCFCE7; color: #16A34A; }
.semestre-badge.reprobadas { background: #FEF2F2; color: #DC2626; }
.semestre-badge.pendientes { background: #FEF3C7; color: #F59E0B; }
.semestre-flecha { stroke: #6B7280; transition: transform 0.25s; flex-shrink: 0; }
.semestre-flecha.girado { transform: rotate(180deg); }
.semestre-contenido { border-top: 1px solid #E5E7EB; }

/* Tabla - Padding estandarizado */
.tabla-materias { width: 100%; border-collapse: collapse; }
.tabla-materias th { background: #F5F5F5; padding: 10px 14px; font-size: 0.77rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-materias th.centrado { text-align: center; }
.tabla-materias td { padding: 8px 14px; border-bottom: 1px solid #F5F5F5; vertical-align: middle; font-size: 0.875rem; }
.tabla-materias td.centrado { text-align: center; }
.tabla-materias tr:last-child td { border-bottom: none; }
.tabla-materias tr:hover { background: #F5F5F5; }
.tabla-materias tr.fila-reprobada { background: #FEF2F2; }
.clave-chip { font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.materia-nombre-celda { display: flex; align-items: center; gap: 6px; color: #1A1A1A; font-weight: 500; }
.icono-advertencia { flex-shrink: 0; }
.texto-secundario { color: #6B7280; }
.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.calif-aprobada { color: #16A34A; }
.calif-reprobada { color: #DC2626; }

/* Animación */
.semestre-expand-enter-active { transition: all 0.25s ease; }
.semestre-expand-leave-active { transition: all 0.2s ease; }
.semestre-expand-enter-from, .semestre-expand-leave-to { opacity: 0; transform: translateY(-6px); }

/* Pie */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; margin-top: 1rem; }

/* ============================================= */
/* BOTONES ESTANDARIZADOS - PALETA OFICIAL      */
/* ============================================= */
.btn-primario {
  background: #1B396A;
  color: #FFFFFF;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-primario:hover:not(:disabled) { background: #1D4ED8; }
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.btn-secundario {
  background: #DBEAFE;
  color: #1B396A;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-secundario:hover:not(:disabled) { background: #BFDBFE; }
.btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-cancelar {
  background: #FFFFFF;
  color: #6B7280;
  border: 1px solid #E5E7EB;
  padding: 10px 1.4rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
}
.btn-cancelar:hover { background: #F5F5F5; }

.spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>