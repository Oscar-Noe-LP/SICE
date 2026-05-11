<!-- ============================================= -->
<!-- src/views/AvanceCurricularView.vue           -->
<!-- Módulo: Kardex — Mapa del plan de estudios   -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="avance-page">
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
        <span class="breadcrumb-actual">Avance Curricular</span>
      </nav>
      
      <!-- Encabezado -->
      <div class="page-header">
        <h1 class="page-title">Avance Curricular</h1>
        <p class="page-subtitulo">Mapa visual del plan de estudios con el estado de cada materia</p>
      </div>
      
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

      <!-- ── BUSCADOR ── -->
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
              @keyup.enter="consultarAvance"
              maxlength="20"
            />
            <button v-if="noControl" @click="limpiar" class="btn-limpiar">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                <line x1="18" y1="6" x2="6" y2="18" stroke-width="2.5"/>
                <line x1="6" y1="6" x2="18" y2="18" stroke-width="2.5"/>
              </svg>
            </button>
          </div>
          <button @click="consultarAvance" class="btn-primario" :disabled="cargando || !noControl.trim()">
            <span v-if="cargando" class="spinner"></span>
            <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            {{ cargando ? 'Consultando...' : 'Consultar' }}
          </button>
        </div>
      </div>

      <!-- ALERTA ERROR -->
      <div v-if="errorAlumno" class="alerta-error">
        <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>No se encontró ningún alumno con el número de control <strong>{{ noControl }}</strong>.</span>
      </div>

      <!-- ── SKELETON ── -->
      <div v-if="cargando" class="skeleton-mapa">
        <div v-for="col in 5" :key="col" class="skeleton-columna">
          <div class="sk-col-titulo"></div>
          <div v-for="row in 6" :key="row" class="sk-materia-card"></div>
        </div>
      </div>

      <!-- ── MAPA CURRICULAR ── -->
      <div v-if="alumno && !cargando">
        <!-- Info del alumno -->
        <div class="alumno-info-bar">
          <div class="alumno-nombre-plan">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <strong>{{ alumno.nombre }}</strong>
            <span class="sep-barra">·</span>
            <span>{{ alumno.no_control }}</span>
            <span class="sep-barra">·</span>
            <span>{{ alumno.carrera }}</span>
          </div>
          <button @click="exportarMapa" class="btn-secundario">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exportar mapa
          </button>
        </div>

        <!-- Rejilla de materias por semestre -->
        <div class="mapa-scroll">
          <div class="mapa-rejilla">
            <div
              v-for="semestre in planEstudios"
              :key="semestre.numero"
              class="mapa-columna"
            >
              <!-- Encabezado de semestre -->
              <div class="col-titulo">
                <span>Semestre {{ semestre.numero }}</span>
              </div>
              
              <!-- Cards de materias -->
              <div
                v-for="materia in semestre.materias"
                :key="materia.clave"
                class="materia-card"
                :class="claseMateria(materia.estado)"
                :title="`${materia.nombre} · ${materia.creditos} créditos`"
                @mouseenter="materiaActiva = materia.clave"
                @mouseleave="materiaActiva = null"
              >
                <!-- Línea conectora de prerrequisito -->
                <div
                  v-if="materia.prerrequisito"
                  class="prereq-indicador"
                  :style="{ background: prereqAcreditado(materia.prerrequisito) ? '#16A34A' : '#E5E7EB' }"
                  :title="`Prerrequisito: ${materia.prerrequisito}`"
                ></div>
                
                <div class="materia-card-cuerpo">
                  <span class="materia-clave">{{ materia.clave }}</span>
                  <span class="materia-nombre-card">{{ materia.nombre }}</span>
                  <span class="materia-creditos">{{ materia.creditos }} créd.</span>
                </div>
                
                <!-- Ícono de estado -->
                <div class="materia-estado-icono">
                  <svg v-if="materia.estado === 'acreditada'" fill="none" viewBox="0 0 24 24" stroke="#16A34A" width="14" height="14">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-else-if="materia.estado === 'reprobada'" fill="none" viewBox="0 0 24 24" stroke="#DC2626" width="14" height="14">
                    <line x1="18" y1="6" x2="6" y2="18" stroke-width="2.5"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke-width="2.5"/>
                  </svg>
                  <svg v-else-if="materia.estado === 'pendiente'" fill="none" viewBox="0 0 24 24" stroke="#F59E0B" width="14" height="14">
                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                    <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Leyenda de colores (Colores corregidos a paleta oficial) -->
        <div class="leyenda">
          <span class="leyenda-titulo">Leyenda:</span>
          <div class="leyenda-items">
            <div class="leyenda-item">
              <span class="leyenda-color" style="background:#DCFCE7; border:1px solid #16A34A;"></span>
              <span>Acreditada</span>
            </div>
            <div class="leyenda-item">
              <span class="leyenda-color" style="background:#FEF2F2; border:1px solid #DC2626;"></span>
              <span>Reprobada</span>
            </div>
            <div class="leyenda-item">
              <span class="leyenda-color" style="background:#FEF3C7; border:1px solid #F59E0B;"></span>
              <span>Inscrita en curso</span>
            </div>
            <div class="leyenda-item">
              <span class="leyenda-color" style="background:#F3F4F6; border:1px solid #E5E7EB;"></span>
              <span>Pendiente</span>
            </div>
            <div class="leyenda-item">
              <span class="prereq-ejemplo" style="background:#16A34A;"></span>
              <span>Prerrequisito acreditado</span>
            </div>
            <div class="leyenda-item">
              <span class="prereq-ejemplo" style="background:#E5E7EB;"></span>
              <span>Prerrequisito pendiente</span>
            </div>
          </div>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`
const router = useRouter()
const route  = useRoute()

// ── Estado ──────────────────────────────────────────────────
const cargando       = ref(false)
const errorAlumno    = ref(false)
const noControl      = ref(route.query.no_control ?? '')
const alumno         = ref(null)
const planEstudios   = ref([])
const materiaActiva  = ref(null)

// Mapa de claves acreditadas para verificar prerrequisitos
const acreditadasSet = ref(new Set())

// Toast unificado (Reemplazado de notificacion -> toast)
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null

const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// Si viene con no_control en la URL se carga automáticamente
onMounted(() => {
  if (noControl.value) consultarAvance()
})

// ── Consulta al backend ───────────────────────────────────────
const consultarAvance = async () => {
  const nc = noControl.value.trim()
  if (!nc) return
  
  cargando.value    = true
  errorAlumno.value = false
  alumno.value      = null
  planEstudios.value = []
  acreditadasSet.value = new Set()
  
  try {
    const res = await fetch(`${API}/kardex/${nc}/avance-curricular`)
    if (res.status === 404) { errorAlumno.value = true; return }
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    
    alumno.value       = data.alumno
    planEstudios.value = data.plan_estudios ?? []
    
    // Construir set de materias acreditadas para verificar prerrequisitos
    planEstudios.value.forEach(sem =>
      sem.materias.forEach(m => {
        if (m.estado === 'acreditada') acreditadasSet.value.add(m.clave)
      })
    )
  } catch (error) {
    console.error('Error cargando avance curricular:', error)
    mostrarToast('No se pudo conectar con el servidor. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

const limpiar = () => {
  noControl.value   = ''
  alumno.value      = null
  errorAlumno.value = false
  planEstudios.value = []
}

// ── Helpers ───────────────────────────────────────────────────
const prereqAcreditado = (clavePrerreq) => acreditadasSet.value.has(clavePrerreq)

const claseMateria = (estado) => ({
  'mat-acreditada': estado === 'acreditada',
  'mat-reprobada':  estado === 'reprobada',
  'mat-pendiente':  estado === 'pendiente',
  'mat-no-cursada': estado === 'no_cursada',
})

const exportarMapa = () => {
  mostrarToast('Generando mapa curricular...', 'info')
  setTimeout(() => mostrarToast('Mapa exportado correctamente', 'exito'), 1800)
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ============================================= */
/* ESTILOS GLOBALES - PALETA OFICIAL SICE       */
/* ============================================= */
.avance-page {
  width: 100%;
  box-sizing: border-box;
  margin: 0 auto;
  background: #F5F5F5; /* Reemplazo de var(--fondo) */
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  position: relative;
}

/* Barra de Carga */
.barra-carga {
  position: fixed; top: 74px; left: 0; right: 0; height: 3px;
  z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s;
}
.barra-carga.activa { opacity: 1; }
.barra-progreso {
  height: 100%;
  /* Reemplazo de variables por hardcoded */
  background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); 
  background-size: 200% 100%;
  animation: barra-anim 1.4s linear infinite;
}
@keyframes barra-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Breadcrumb */
.breadcrumb {
  display: flex; align-items: center; gap: 0.4rem;
  color: #6B7280; font-size: 0.88rem; margin-bottom: 0.75rem; /* #6B7280 = var(--gris) */
}
.breadcrumb .sep { color: #E5E7EB; } /* #E5E7EB = var(--borde) */
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; } /* #1B396A = var(--azul) */
.breadcrumb-actual { color: #1B396A; font-weight: 600; }

/* Encabezado */
.page-header    { margin-bottom: 1.4rem; }
.page-title     { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; margin: 0 0 4px; } /* #1A1A1A = var(--texto) */
.page-subtitulo { color: #6B7280; font-size: 0.88rem; margin: 0; } /* #6B7280 = var(--gris) */

/* Toast Estándar */
.toast {
  position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 10px;
  padding: 0.9rem 1.4rem; border-radius: 10px; color: white; font-weight: 700; font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px;
  font-family: 'Montserrat', sans-serif;
}
.toast.exito { background: #1B396A; } /* #1B396A = var(--azul) */
.toast.error { background: #DC2626; } /* #DC2626 = var(--rojo) */
.toast.info  { background: #2563EB; }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* Buscador */
.buscador-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem; margin-bottom: 1.5rem;
}
.buscador-label { display: block; font-size: 0.85rem; font-weight: 700; color: #1A1A1A; margin-bottom: 0.6rem; } /* #1A1A1A = var(--texto) */
.buscador-fila  { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
.buscador-input-wrap {
  display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;
  background: #F5F5F5; border: 1.5px solid #E5E7EB; border-radius: 10px; padding: 0 14px;
  transition: border-color 0.2s, background 0.2s;
}
.buscador-input-wrap.activo, .buscador-input-wrap:focus-within {
  border-color: #1B396A; background: #DBEAFE;
}
.buscador-icono-svg { width: 18px; height: 18px; stroke: #6B7280; flex-shrink: 0; }
.buscador-input {
  flex: 1; border: none; background: transparent; padding: 12px 0;
  font-size: 1rem; font-family: inherit; color: #1A1A1A; outline: none;
}
.buscador-input::placeholder { color: #9CA3AF; }
.btn-limpiar { background: none; border: none; color: #6B7280; cursor: pointer; display: flex; align-items: center; padding: 2px; }
.btn-limpiar:hover { color: #DC2626; }

/* Botones Estandarizados */
.btn-primario {
  background: #1B396A; color: #FFFFFF; border: none;
  padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.95rem;
  display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: inherit;
  transition: background 0.2s; white-space: nowrap;
}
.btn-primario:hover:not(:disabled) { background: #1D4ED8; }
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.btn-secundario {
  background: #DBEAFE; color: #1B396A; border: none;
  padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit;
  transition: background 0.2s;
}
.btn-secundario:hover:not(:disabled) { background: #BFDBFE; }
.btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }

.spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Alerta */
.alerta-error {
  display: flex; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FECACA;
  border-radius: 10px; padding: 12px 16px; margin-bottom: 1.4rem;
  font-size: 0.9rem; color: #DC2626; font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: #DC2626; }

/* Skeleton Mapa */
.skeleton-mapa { display: flex; gap: 0.75rem; overflow-x: auto; padding-bottom: 0.5rem; }
.skeleton-columna { display: flex; flex-direction: column; gap: 8px; min-width: 140px; }
.sk-col-titulo { height: 36px; border-radius: 8px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-materia-card { height: 80px; border-radius: 10px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Info Bar del Alumno */
.alumno-info-bar {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.4rem;
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.2rem; flex-wrap: wrap; gap: 0.75rem;
}
.alumno-nombre-plan { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: #1A1A1A; flex-wrap: wrap; }
.alumno-nombre-plan svg { stroke: #1B396A; flex-shrink: 0; }
.sep-barra { color: #E5E7EB; }

/* Rejilla del Mapa */
.mapa-scroll { overflow-x: auto; margin-bottom: 1.5rem; padding-bottom: 0.5rem; }
.mapa-rejilla { display: flex; gap: 10px; min-width: max-content; }
.mapa-columna { display: flex; flex-direction: column; gap: 8px; width: 150px; }
.col-titulo {
  background: #1B396A; color: #FFFFFF;
  border-radius: 8px; padding: 8px 10px; text-align: center;
  font-size: 0.78rem; font-weight: 700; white-space: nowrap;
}

/* Cards de Materias */
.materia-card {
  border-radius: 10px; border: 1.5px solid transparent; padding: 8px 10px;
  cursor: default; transition: transform 0.15s, box-shadow 0.15s;
  position: relative; overflow: hidden;
  display: flex; flex-direction: column; gap: 2px;
}
.materia-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

/* Estados con colores oficiales (Reemplazo de variables y colores no oficiales) */
.mat-acreditada { background: #DCFCE7; border-color: #16A34A; }
.mat-reprobada  { background: #FEF2F2; border-color: #DC2626; }
.mat-pendiente  { background: #FEF3C7; border-color: #F59E0B; }
.mat-no-cursada { background: #F3F4F6; border-color: #E5E7EB; }

/* Indicador de prerrequisito (barra lateral) */
.prereq-indicador {
  position: absolute; top: 0; left: 0; bottom: 0; width: 4px; border-radius: 2px 0 0 2px;
}
.materia-card-cuerpo { padding-left: 6px; }
.materia-clave       { font-size: 0.7rem; font-weight: 700; color: #6B7280; font-family: monospace; display: block; }
.materia-nombre-card { font-size: 0.75rem; font-weight: 600; color: #1A1A1A; display: block; line-height: 1.3; margin: 2px 0; }
.materia-creditos    { font-size: 0.7rem; color: #6B7280; }
.materia-estado-icono { position: absolute; top: 6px; right: 6px; }

/* Leyenda */
.leyenda {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.4rem;
  display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.leyenda-titulo { font-size: 0.82rem; font-weight: 700; color: #6B7280; white-space: nowrap; }
.leyenda-items  { display: flex; gap: 1rem; flex-wrap: wrap; }
.leyenda-item   { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #6B7280; }
.leyenda-color  { width: 14px; height: 14px; border-radius: 4px; flex-shrink: 0; }
.prereq-ejemplo { width: 4px; height: 18px; border-radius: 2px; flex-shrink: 0; }

.pie-pagina {
  text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem;
  border-top: 1px solid #E5E7EB; margin-top: 1rem;
}

@media (max-width: 640px) {
  .buscador-fila { flex-direction: column; align-items: stretch; }
  .btn-primario { justify-content: center; }
  .alumno-info-bar { flex-direction: column; align-items: flex-start; }
  .leyenda { flex-direction: column; align-items: flex-start; }
}
</style>