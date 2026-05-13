<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="dashboard-page">

      <!-- ── Barra de carga top ── -->
      <div class="barra-carga" :class="{ activa: cargando }" aria-hidden="true">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Encabezado ── -->
      <div class="inicio-header">
        <div class="header-texto">
          <h1 class="page-title">Inicio</h1>
          <p class="welcome-text">
            Bienvenido al Sistema Integral de Control Escolar
          </p>
        </div>
        <time class="fecha-actual" :datetime="fechaISO">{{ fechaHoy }}</time>
      </div>

      <!-- ── Búsqueda rápida de alumno (acceso desde Dashboard) ── -->
      <div class="busqueda-rapida-card" role="search" aria-label="Búsqueda rápida de alumno">
        <div class="busqueda-rapida-texto">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <div>
            <p class="busqueda-label">Consulta rápida de alumno</p>
            <p class="busqueda-sub">Ingresa el número de control para ver toda la información</p>
          </div>
        </div>
        <div class="busqueda-rapida-form">
          <div class="busqueda-input-wrap">
            <svg class="busqueda-icono-input" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="17" height="17" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              type="text"
              v-model.trim="busquedaControl"
              class="busqueda-input"
              placeholder="Ej. 25000001"
              maxlength="8"
              inputmode="numeric"
              aria-label="Número de control del alumno"
              @keydown.enter="irAKardex"
              @input="busquedaControl = busquedaControl.replace(/\D/g, '')"
            />
          </div>
          <button
            class="btn-buscar"
            @click="irAKardex"
            :disabled="busquedaControl.length < 8"
            type="button"
            aria-label="Buscar alumno"
          >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </button>
        </div>
      </div>

      <!-- ── Alerta de error general ── -->
      <transition name="fade">
        <div v-if="error" class="alerta-error" role="alert">
          <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ error }}
        </div>
      </transition>

      <!-- ── KPIs ── -->
      <div class="kpi-grid" role="list" aria-label="Indicadores del sistema">
        <div
          v-for="(kpi, i) in kpis"
          :key="i"
          class="kpi-card"
          role="listitem"
        >
          <div class="kpi-icono-wrapper" :style="{ background: kpi.fondo }" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" class="kpi-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path :d="kpi.iconPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
          </div>
          <div class="kpi-contenido">
            <p class="kpi-etiqueta">{{ kpi.title }}</p>
            <!-- Skeleton del valor -->
            <div v-if="cargando" class="kpi-skeleton" aria-hidden="true"></div>
            <p v-else class="kpi-valor">{{ kpi.value }}</p>
            <p
              v-if="kpi.trend && !cargando"
              class="kpi-tendencia"
              :class="{ positivo: kpi.trend.includes('+'), negativo: kpi.trend.includes('-') }"
            >
              {{ kpi.trend }}
            </p>
          </div>
        </div>
      </div>

      <!-- ── Gráficas ── -->
      <div class="fila-graficas">
        <!-- Alumnos por carrera -->
        <div class="grafica-card">
          <h3 class="grafica-titulo">Alumnos por Carrera</h3>
          <div v-if="carreraData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in carreraData" :key="i" class="barra-item">
              <div class="barra-etiqueta" :title="item.carrera">{{ item.carrera }}</div>
              <div class="barra-contenedor" role="progressbar" :aria-valuenow="item.total" :aria-label="item.carrera">
                <div class="barra-relleno" :style="{ width: item.porcentaje + '%' }"></div>
              </div>
              <div class="barra-valor">{{ item.total }}</div>
            </div>
          </div>
          <div v-else class="estado-vacio-grafica" aria-live="polite">
            <p>Sin datos disponibles</p>
          </div>
        </div>

        <!-- Alumnos por semestre -->
        <div class="grafica-card">
          <h3 class="grafica-titulo">Alumnos por Semestre</h3>
          <div v-if="semestreData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in semestreData" :key="i" class="barra-item">
              <div class="barra-etiqueta">{{ item.semestre }}°</div>
              <div class="barra-contenedor" role="progressbar" :aria-valuenow="item.cantidad" :aria-label="`${item.semestre}° semestre`">
                <div class="barra-relleno barra-acento" :style="{ width: calcularPorcentajeSemestre(item.cantidad) + '%' }"></div>
              </div>
              <div class="barra-valor">{{ item.cantidad }}</div>
            </div>
          </div>
          <div v-else class="estado-vacio-grafica">
            <p>Sin datos disponibles</p>
          </div>
        </div>
      </div>

      <!-- ── Fila inferior ── -->
      <div class="fila-inferior">

        <!-- Actividad reciente / Bitácora -->
        <div class="panel-card">
          <div class="bitacora-header">
            <h3 class="panel-titulo">Actividad Reciente</h3>
            <router-link to="/bitacora" class="btn-ver-bitacora" aria-label="Ver bitácora completa">
              Ver todo →
            </router-link>
          </div>

          <!-- Estado: cargando bitácora -->
          <div v-if="cargandoBitacora" class="bitacora-cargando" aria-busy="true">
            <div class="spinner-bitacora" aria-hidden="true"></div>
            <span>Cargando actividad...</span>
          </div>

          <!-- Estado: error de bitácora (placeholder) -->
          <div v-else-if="errorBitacora" class="lista-bitacora">
            <div v-for="i in 3" :key="'ph-'+i" class="item-bitacora item-placeholder">
              <div class="avatar-bitacora avatar-gris" aria-hidden="true">?</div>
              <div class="info-bitacora">
                <p class="bitacora-desc" style="color:#9CA3AF">Esperando conexión con el servidor...</p>
              </div>
            </div>
          </div>

          <!-- Estado: sin actividad -->
          <div v-else-if="bitacoraReciente.length === 0" class="estado-vacio-grafica" aria-live="polite">
            <p>Sin actividad reciente</p>
          </div>

          <!-- Lista de actividad -->
          <div v-else class="lista-bitacora" role="list">
            <div
              v-for="(item, i) in bitacoraReciente"
              :key="item.id_bitacora || i"
              class="item-bitacora"
              role="listitem"
            >
              <div class="avatar-bitacora" aria-hidden="true">
                {{ item.usuario ? item.usuario.charAt(0).toUpperCase() : '?' }}
              </div>
              <div class="info-bitacora">
                <div class="bitacora-fila-superior">
                  <span class="bitacora-usuario">{{ item.usuario }}</span>
                  <span class="accion-badge-mini" :class="claseAccion(item.accion)">{{ item.accion }}</span>
                </div>
                <p class="bitacora-desc">{{ item.descripcion }}</p>
                <div class="bitacora-fila-inferior">
                  <span class="bitacora-modulo">{{ item.modulo }}</span>
                  <time class="bitacora-tiempo" :datetime="item.fecha_hora">{{ tiempoRelativo(item.fecha_hora) }}</time>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="panel-card">
          <h3 class="panel-titulo">Acciones Rápidas</h3>
          <div class="grilla-acciones" role="list">
            <button
              v-for="accion in accionesRapidas"
              :key="accion.label"
              class="btn-accion"
              :class="{ 'btn-primario-accion': accion.primario }"
              @click="accion.handler"
              type="button"
              role="listitem"
              :aria-label="accion.label"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="accion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path :d="accion.iconPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
              </svg>
              {{ accion.label }}
            </button>
          </div>
        </div>

      </div><!-- /fila-inferior -->

    </div>
  </MainLayout>
</template>

<script setup>

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router  = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Estado general ──────────────────────────────────────────────────────
const cargando = ref(true)
const error    = ref(null)

// ── Búsqueda rápida de alumno ───────────────────────────────────────────
const busquedaControl = ref('')

/**
 * Redirige al kardex del alumno buscado.
 * Solo requiere que el usuario ingrese el número de control UNA sola vez.
 */
const irAKardex = () => {
  const nc = busquedaControl.value.trim()
  if (nc.length < 8) return
  router.push(`/kardex/${nc}`)
}

// ── Fechas ──────────────────────────────────────────────────────────────
const fechaHoy = computed(() =>
  new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
)
const fechaISO = computed(() => new Date().toISOString().split('T')[0])

// ── KPIs ─────────────────────────────────────────────────────────────────
const kpis = ref([
  { title: 'Alumnos activos',  value: '0', iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', fondo: '#DBEAFE', trend: '' },
  { title: 'Inscripciones',    value: '0', iconPath: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',                         fondo: '#DCFCE7', trend: '' },
  { title: 'Baja temporal',    value: '0', iconPath: 'M13 10V3L4 14h7v7l9-11h-7z',                                                                                     fondo: '#FEF3C7', trend: '' },
  { title: 'Baja definitiva',  value: '0', iconPath: 'M6 18L18 6M6 6l12 12',                                                                                           fondo: '#FEE2E2', trend: '' },
  { title: 'Grupos activos',   value: '0', iconPath: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', fondo: '#F3E8FF', trend: '' },
  { title: 'Promedio general', value: '0', iconPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', fondo: '#E0F2FE', trend: '' }
])

// ── Datos de gráficas (fallback si backend no responde) ────────────────
const carreraDataDefault = [
  { carrera: 'Sist. Computacional', total: 312, porcentaje: 100 },
  { carrera: 'Industrial',          total: 268, porcentaje: 86  },
  { carrera: 'Eléctrica',           total: 198, porcentaje: 63  },
  { carrera: 'Mecánica',            total: 174, porcentaje: 56  },
  { carrera: 'Gest. Empresarial',   total: 156, porcentaje: 50  },
  { carrera: 'Bioquímica',          total: 176, porcentaje: 56  }
]
const semestreDataDefault = [
  { semestre: '1', cantidad: 180 },
  { semestre: '2', cantidad: 165 },
  { semestre: '3', cantidad: 158 },
  { semestre: '4', cantidad: 144 },
  { semestre: '5', cantidad: 152 },
  { semestre: '6', cantidad: 138 },
  { semestre: '7', cantidad: 175 },
  { semestre: '8', cantidad: 172 }
]

const carreraData  = ref([...carreraDataDefault])
const semestreData = ref([...semestreDataDefault])

const calcularPorcentajeSemestre = (cant) => {
  const max = Math.max(...semestreData.value.map(s => s.cantidad), 1)
  return Math.round((cant / max) * 100)
}

// ── Bitácora reciente ────────────────────────────────────────────────────
const bitacoraReciente  = ref([])
const cargandoBitacora  = ref(false)
const errorBitacora     = ref(false)

const cargarBitacoraReciente = async () => {
  cargandoBitacora.value = true
  errorBitacora.value    = false
  try {
    const res  = await fetch(`${API_URL}/api/bitacora?limit=5`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    bitacoraReciente.value = Array.isArray(data) ? data : (data.bitacora || [])
  } catch {
    errorBitacora.value = true
  } finally {
    cargandoBitacora.value = false
  }
}

const tiempoRelativo = (fechaStr) => {
  if (!fechaStr) return ''
  const diff = Date.now() - new Date(fechaStr).getTime()
  const min  = Math.floor(diff / 60000)
  if (min < 1)   return 'Ahora'
  if (min < 60)  return `Hace ${min} min`
  const hrs = Math.floor(min / 60)
  if (hrs < 24)  return `Hace ${hrs} h`
  const dias = Math.floor(hrs / 24)
  return `Hace ${dias} día${dias > 1 ? 's' : ''}`
}

const claseAccion = (accion = '') => {
  const a = accion.toLowerCase()
  if (a.includes('login') || a.includes('acceso'))    return 'accion-login'
  if (a.includes('cre') || a.includes('registr'))     return 'accion-creacion'
  if (a.includes('edit') || a.includes('actualiz'))   return 'accion-edicion'
  if (a.includes('elim') || a.includes('bor'))        return 'accion-eliminacion'
  return 'accion-default'
}

// ── Carga de KPIs desde el backend ──────────────────────────────────────
const cargarKPIs = async () => {
  cargando.value = true
  error.value    = null
  try {
    const res  = await fetch(`${API_URL}/api/dashboard/kpis`)
    if (!res.ok) throw new Error('Error al cargar KPIs')
    const data = await res.json()

    if (data.alumnos_activos   !== undefined) kpis.value[0].value = data.alumnos_activos.toString()
    if (data.inscripciones     !== undefined) kpis.value[1].value = data.inscripciones.toString()
    if (data.baja_temporal     !== undefined) kpis.value[2].value = data.baja_temporal.toString()
    if (data.baja_definitiva   !== undefined) kpis.value[3].value = data.baja_definitiva.toString()
    if (data.grupos_activos    !== undefined) kpis.value[4].value = data.grupos_activos.toString()
    if (data.promedio_general  !== undefined) kpis.value[5].value = data.promedio_general.toString()

    if (data.carrera_data)  carreraData.value  = data.carrera_data
    if (data.semestre_data) semestreData.value = data.semestre_data
  } catch (err) {
    console.error('[Dashboard] Error al cargar KPIs:', err)
    // No mostramos error visible; los fallback se quedan en 0/default
  } finally {
    cargando.value = false
  }
}

// ── Acciones rápidas ─────────────────────────────────────────────────────
/** Definición declarativa de las acciones del panel */
const accionesRapidas = [
  {
    label:    'Nueva inscripción',
    primario: true,
    iconPath: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    handler:  () => router.push('/formulario-alumno')
  },
  {
    label:    'Lista de alumnos',
    iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    handler:  () => router.push('/alumnos')
  },
  {
    label:    'Gestión de grupos',
    iconPath: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    handler:  () => router.push('/gestion-grupos')
  },
  {
    label:    'Cargar calificaciones',
    iconPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    handler:  () => router.push('/calificaciones')
  }
]

// ── Lifecycle ─────────────────────────────────────────────────────────────
onMounted(() => {
  cargarKPIs()
  cargarBitacoraReciente()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   VARIABLES — paleta SICE
══════════════════════════════════════════════════════ */
.dashboard-page {
  --azul:       #1B396A;
  --azul-hover: #15305A;
  --azul-suave: #DBEAFE;
  --verde:      #16A34A;
  --rojo:       #DC2626;
  --amarillo:   #F59E0B;
  --borde:      #E5E7EB;
  --fondo:      #F9FAFB;
  --texto:      #111827;
  --gris:       #6B7280;
  --radio:      12px;

  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Barra de carga ── */
.barra-carga { position:fixed; top:0; left:0; right:0; height:3px; z-index:9999; opacity:0; transition:opacity 0.2s; pointer-events:none; }
.barra-carga.activa { opacity:1; }
.barra-progreso { height:100%; background:var(--azul); animation:progreso-carga 1.5s ease-in-out infinite; }
@keyframes progreso-carga { 0%{width:0%;opacity:1} 70%{width:85%;opacity:1} 100%{width:100%;opacity:0} }

/* ── Encabezado ── */
.inicio-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.4rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.page-title { font-size: 1.75rem; font-weight: 700; color: var(--texto); margin: 0 0 4px; }
.welcome-text { font-size: 0.9rem; color: var(--gris); margin: 0; }
.fecha-actual {
  font-size: 0.85rem;
  color: var(--gris);
  font-weight: 500;
  text-transform: capitalize;
  white-space: nowrap;
  background: var(--fondo);
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid var(--borde);
}

/* ── Búsqueda rápida ── */
.busqueda-rapida-card {
  background: linear-gradient(135deg, var(--azul) 0%, #1D4ED8 100%);
  border-radius: var(--radio);
  padding: 1.2rem 1.6rem;
  margin-bottom: 1.4rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  box-shadow: 0 6px 20px rgba(27,57,106,0.25);
}
.busqueda-rapida-texto {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 200px;
}
.busqueda-rapida-texto svg { stroke: rgba(255,255,255,0.8); flex-shrink: 0; }
.busqueda-label { font-size: 0.95rem; font-weight: 700; color: #FFFFFF; margin: 0 0 2px; }
.busqueda-sub   { font-size: 0.8rem; color: rgba(255,255,255,0.7); margin: 0; }
.busqueda-rapida-form { display: flex; gap: 8px; align-items: center; }
.busqueda-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.busqueda-icono-input {
  position: absolute;
  left: 12px;
  stroke: #9CA3AF;
  pointer-events: none;
}
.busqueda-input {
  padding: 10px 14px 10px 38px;
  border-radius: 8px;
  border: 1.5px solid rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.12);
  color: white;
  font-family: 'Montserrat', monospace;
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  width: 180px;
  outline: none;
  transition: border-color 0.2s, background 0.2s;
  backdrop-filter: blur(4px);
}
.busqueda-input::placeholder { color: rgba(255,255,255,0.5); font-weight: 400; letter-spacing: 0; }
.busqueda-input:focus { border-color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.18); }
.btn-buscar {
  display: flex; align-items: center; gap: 6px;
  padding: 10px 18px;
  background: #FFFFFF; color: var(--azul);
  border: none; border-radius: 8px;
  font-weight: 700; font-size: 0.875rem;
  cursor: pointer; font-family: inherit;
  transition: background 0.15s, transform 0.1s;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.btn-buscar:hover:not(:disabled) { background: #F0F7FF; transform: translateY(-1px); }
.btn-buscar:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn-buscar svg { stroke: var(--azul); }

/* ── Alerta de error ── */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: #FEF2F2; border: 1px solid #FECACA;
  border-radius: var(--radio); padding: 12px 16px;
  margin-bottom: 1.2rem; font-size: 0.875rem;
  color: var(--rojo); font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: var(--rojo); }

/* ── KPI Grid ── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1rem;
  margin-bottom: 1.4rem;
}
.kpi-card {
  background: #FFFFFF;
  border-radius: var(--radio);
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 1px solid var(--borde);
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
.kpi-icono-wrapper {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-icono { width: 20px; height: 20px; stroke: var(--azul); }
.kpi-contenido { flex: 1; min-width: 0; }
.kpi-etiqueta { font-size: 0.78rem; color: var(--gris); font-weight: 500; margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kpi-valor    { font-size: 1.5rem; font-weight: 800; color: var(--texto); margin: 0; line-height: 1.1; }
.kpi-skeleton { width: 60px; height: 28px; border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; margin: 2px 0; }
.kpi-tendencia { font-size: 0.75rem; font-weight: 600; margin: 2px 0 0; }
.positivo { color: var(--verde); }
.negativo { color: var(--rojo); }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Gráficas ── */
.fila-graficas { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.grafica-card {
  background: #FFFFFF; border-radius: var(--radio);
  border: 1px solid var(--borde); padding: 1.4rem 1.6rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.grafica-titulo { font-size: 0.95rem; font-weight: 700; color: var(--texto); margin: 0 0 1.2rem; }
.grafica-barras { display: flex; flex-direction: column; gap: 10px; }
.barra-item { display: flex; align-items: center; gap: 10px; }
.barra-etiqueta { width: 130px; font-size: 0.8rem; color: var(--gris); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-shrink: 0; }
.barra-contenedor { flex: 1; background: var(--fondo); border-radius: 4px; height: 8px; overflow: hidden; }
.barra-relleno { height: 100%; background: var(--azul); border-radius: 4px; transition: width 0.6s ease; }
.barra-acento  { background: var(--verde); }
.barra-valor   { font-size: 0.8rem; font-weight: 700; color: var(--texto); min-width: 30px; text-align: right; flex-shrink: 0; }
.estado-vacio-grafica { display: flex; align-items: center; justify-content: center; padding: 2rem; color: var(--gris); font-size: 0.875rem; }

/* ── Fila inferior ── */
.fila-inferior { display: grid; grid-template-columns: 1fr 380px; gap: 1rem; }
.panel-card {
  background: #FFFFFF; border-radius: var(--radio);
  border: 1px solid var(--borde); padding: 1.4rem 1.6rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.panel-titulo { font-size: 0.95rem; font-weight: 700; color: var(--texto); margin: 0; }

/* Bitácora */
.bitacora-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.btn-ver-bitacora { font-size: 0.82rem; font-weight: 600; color: var(--azul); text-decoration: none; transition: color 0.15s; }
.btn-ver-bitacora:hover { color: #1D4ED8; }

.bitacora-cargando { display: flex; align-items: center; gap: 10px; padding: 1.5rem 0; color: var(--gris); font-size: 0.875rem; }
.spinner-bitacora { width: 18px; height: 18px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.75s linear infinite; flex-shrink: 0; }
@keyframes girar { to { transform: rotate(360deg); } }

.lista-bitacora { display: flex; flex-direction: column; gap: 2px; }
.item-bitacora { display: flex; gap: 10px; align-items: flex-start; padding: 8px; border-radius: 8px; transition: background 0.15s; }
.item-bitacora:hover { background: var(--fondo); }
.item-placeholder { opacity: 0.5; }
.avatar-bitacora { width: 32px; height: 32px; border-radius: 50%; background: var(--azul); color: white; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.avatar-gris { background: #9CA3AF; }
.info-bitacora { flex: 1; min-width: 0; }
.bitacora-fila-superior { display: flex; align-items: center; gap: 6px; margin-bottom: 2px; flex-wrap: wrap; }
.bitacora-usuario { font-weight: 600; font-size: 0.85rem; color: var(--texto); }
.bitacora-desc { margin: 0 0 3px; font-size: 0.8rem; color: var(--gris); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bitacora-fila-inferior { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.bitacora-modulo { font-size: 0.75rem; color: #9CA3AF; font-weight: 500; }
.bitacora-tiempo { font-size: 0.75rem; color: #9CA3AF; white-space: nowrap; }
.accion-badge-mini { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.accion-login       { background: var(--azul-suave); color: var(--azul); }
.accion-creacion    { background: #DCFCE7; color: var(--verde); }
.accion-edicion     { background: #FEF3C7; color: var(--amarillo); }
.accion-eliminacion { background: #FEF2F2; color: var(--rojo); }
.accion-default     { background: var(--fondo); color: var(--gris); }

/* Acciones rápidas */
.grilla-acciones { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem; }
.btn-accion {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 8px; padding: 1rem; min-height: 80px;
  background: var(--fondo); border: 1.5px solid var(--borde);
  border-radius: var(--radio); font-size: 0.82rem; font-weight: 600;
  color: var(--texto); cursor: pointer; font-family: inherit;
  transition: all 0.2s; text-align: center;
}
.btn-accion:hover { background: var(--azul-suave); border-color: var(--azul); color: var(--azul); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(27,57,106,0.1); }
.btn-accion.btn-primario-accion { background: var(--azul); color: white; border-color: var(--azul); }
.btn-accion.btn-primario-accion:hover { background: var(--azul-hover); }
.accion-icono { width: 22px; height: 22px; stroke: currentColor; }

/* Fade */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ══ RESPONSIVE ══ */
/* ══════════════════════════════════════
   RESPONSIVE — DashboardView
   Santiago Acosta — Modificaciones SICE
══════════════════════════════════════ */

/* ── Tablet grande (≤1200px) ── */
@media (max-width: 1200px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
}

/* ── Tablet (≤900px) ── */
@media (max-width: 900px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .fila-graficas { grid-template-columns: 1fr; }
  .fila-inferior { grid-template-columns: 1fr; }
  .barra-etiqueta { width: 90px; }
}

/* ── Móvil grande (≤640px) ── */
@media (max-width: 640px) {
  .inicio-header {
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 1rem;
  }

  .page-title { font-size: 1.35rem; }
  .welcome-text { font-size: 0.82rem; }

  .fecha-actual {
    font-size: 0.78rem;
    white-space: normal;
    align-self: flex-start;
  }

  /* Búsqueda rápida en columna */
  .busqueda-rapida-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.2rem;
  }

  .busqueda-rapida-form {
    width: 100%;
    flex-wrap: wrap;
    gap: 8px;
  }

  .busqueda-input-wrap { flex: 1; }
  .busqueda-input {
    width: 100%;
    font-size: 16px; /* evita zoom iOS */
  }

  .btn-buscar { width: 100%; justify-content: center; }

  /* KPIs más compactos */
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .kpi-card { padding: 1rem; gap: 0.75rem; }
  .kpi-valor { font-size: 1.35rem; }
  .kpi-etiqueta { font-size: 0.72rem; }
  .kpi-icono-wrapper { width: 36px; height: 36px; border-radius: 8px; }
  .kpi-icono { width: 18px; height: 18px; }

  /* Gráficas */
  .grafica-card { padding: 1rem 1.2rem; }
  .barra-etiqueta { width: 80px; font-size: 0.75rem; }

  /* Acciones rápidas en 2 columnas */
  .grilla-acciones { grid-template-columns: 1fr 1fr; gap: 0.6rem; }
  .btn-accion { min-height: 72px; font-size: 0.78rem; padding: 0.8rem; }
  .accion-icono { width: 18px; height: 18px; }

  /* Panel card más compacto */
  .panel-card { padding: 1rem 1.2rem; }

  /* Bitácora compacta */
  .bitacora-desc {
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

/* ── Móvil pequeño (≤480px) ── */
@media (max-width: 480px) {
  .page-title { font-size: 1.2rem; }

  /* KPIs en 2 columnas mantenidas pero más compactas */
  .kpi-valor { font-size: 1.2rem; }
  .kpi-etiqueta { font-size: 0.7rem; }
  .kpi-card { padding: 0.85rem; }

  /* Acciones en columna con fila horizontal */
  .grilla-acciones { grid-template-columns: 1fr; }
  .btn-accion {
    flex-direction: row;
    min-height: 52px;
    justify-content: flex-start;
    padding: 12px 14px;
    gap: 10px;
  }

  /* Búsqueda rápida más compacta */
  .busqueda-label { font-size: 0.88rem; }
  .busqueda-sub { display: none; }

  /* Bitácora fila inferior en columna */
  .bitacora-fila-inferior {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
  }
}
</style>

label:    'Nueva inscripción',