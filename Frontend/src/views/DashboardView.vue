<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="dashboard-page">

      <div class="barra-carga" :class="{ activa: state.cargando }" aria-hidden="true">
        <div class="barra-progreso"></div>
      </div>

      <div class="bc">
        <span>SICE</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" class="bc-sep" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <b>INICIO</b>
      </div>

      <div class="greeting">
        <div class="gr-left">
          <h2>{{ saludo }}, {{ nombreUsuario }}</h2>
          <p>ADMINISTRADOR DE CONTROL ESCOLAR · ULTIMA SESION: HOY {{ horaAcceso }}</p>
        </div>
        <div class="periodo-pill">
          <div class="p-dot" aria-hidden="true"></div>
          <span class="p-text">PERIODO ACTIVO:</span>
          <span class="p-name">{{ state.kpis.periodoActivo }}</span>
          <span class="p-text">· {{ fechaCorta }}</span>
        </div>
      </div>

      <StatsGrid
        :stats="statsConfig"
        :cargando="state.cargando"
        @navegar="router.push($event)"
      />

      <div class="carreras-seccion">

        <div class="sec-header">
          <div class="sec-titulo-wrap">
            <h2 class="sec-titulo">CARRERAS</h2>
            <span class="sec-sub">RESUMEN POR CARRERA DEL PERIODO ACTUAL</span>
          </div>
          <router-link to="/gestion-grupos" class="sec-link">
            VER TODOS LOS GRUPOS
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </router-link>
        </div>

        <CareerFilters
          :carreras="state.carreras"
          :carrera-activa="state.carreraActiva"
          :total-alumnos="state.kpis.totalAlumnos"
          @filtrar="setCarreraActiva"
        />

        <div class="carreras-wrap">

          <template v-if="state.cargando">
            <div v-for="i in 6" :key="'sk-'+i" class="cc-sk" aria-hidden="true">
              <div class="sk-barra"></div>
              <div class="sk-body"><div class="sk-ico"></div><div class="sk-lines"><div class="sk-line sk-lg"></div><div class="sk-line sk-sm"></div></div></div>
              <div class="sk-num"></div>
              <div class="sk-bar-h"></div>
              <div class="sk-chips"><div class="sk-chip"></div><div class="sk-chip"></div></div>
            </div>
          </template>

          <template v-else>
            <CareerCard
              v-for="(carrera, i) in carrerasMostradas"
              :key="carrera.id_carrera"
              :carrera="carrera"
              :color="COLORES_CARRERA[i % COLORES_CARRERA.length]"
              :icono="getIcono(carrera.nombre)"
              :activa="state.carreraActiva === carrera.id_carrera"
              @click="setCarreraActiva(carrera.id_carrera)"
            />
          </template>

          <div v-if="!state.cargando && state.carreras.length === 0" class="carreras-vacio">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
            <p>SIN DATOS DE CARRERAS DISPONIBLES</p>
          </div>

        </div>
      </div>

      <div class="row-graficas">

        <div class="card">
          <div class="ch">
            <span class="ct">ALUMNOS POR CARRERA</span>
            <router-link to="/alumnos" class="cl">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
              VER DETALLE
            </router-link>
          </div>
          <div v-if="state.carreraData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in state.carreraData" :key="i" class="bar-r">
              <div class="bl" :title="item.carrera">{{ item.carrera }}</div>
              <div class="bt"><div class="bf" :style="{ width: item.porcentaje+'%', background: COLORES_CARRERA[i%COLORES_CARRERA.length] }"></div></div>
              <div class="bv">{{ item.total }}</div>
            </div>
          </div>
          <div v-else class="ev">SIN DATOS DISPONIBLES</div>
        </div>

        <div class="card">
          <div class="ch">
            <span class="ct">DISTRIBUCION POR SEMESTRE</span>
            <span class="cl">ANALITICA</span>
          </div>
          <div v-if="state.semestreData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in state.semestreData" :key="i" class="bar-r">
              <div class="bl">{{ item.semestre }}° SEMESTRE</div>
              <div class="bt"><div class="bf bf-blue" :style="{ width: calcPctSem(item.cantidad)+'%' }"></div></div>
              <div class="bv">{{ item.cantidad }}</div>
            </div>
          </div>
          <div v-else class="ev">SIN DATOS DISPONIBLES</div>
        </div>

        <div class="card card-bit">
          <div class="ch">
            <span class="ct">ACTIVIDAD RECIENTE</span>
            <router-link to="/bitacora" class="cl">VER TODO</router-link>
          </div>

          <div v-if="state.cargandoBitacora" class="bit-loading">
            <div class="spinner" aria-hidden="true"></div>
            <span>CARGANDO...</span>
          </div>

          <template v-else-if="state.bitacora.length > 0">
            <div
              v-for="(item, i) in state.bitacora"
              :key="item.id_bitacora || i"
              class="bit-item"
              @click="modalItem = item"
              role="button"
              tabindex="0"
              @keydown.enter="modalItem = item"
            >
              <div class="bit-av" aria-hidden="true">
                {{ (item.usuario || item.nombre_usuario || '?').slice(0,2).toUpperCase() }}
              </div>
              <div class="bit-body">
                <div class="bit-r1">
                  <span class="bit-usr">{{ (item.usuario || item.nombre_usuario || '—').toUpperCase() }}</span>
                  <span class="bdg" :class="claseBadge(item.accion)">{{ item.accion?.toUpperCase() }}</span>
                </div>
                <div class="bit-desc">{{ item.accion?.toUpperCase() }}</div>
                <div class="bit-t">{{ tiempoRelativo(item.fecha_hora) }}</div>
              </div>
            </div>
          </template>

          <template v-else-if="state.errorBitacora">
            <div class="bit-item" v-for="i in 3" :key="'ph-'+i">
              <div class="bit-av bit-av-gris" aria-hidden="true">?</div>
              <div class="bit-body"><div class="bit-desc" style="color:#BDBDBD">ESPERANDO CONEXION CON EL SERVIDOR...</div></div>
            </div>
          </template>

          <div v-else class="ev">SIN ACTIVIDAD RECIENTE</div>
        </div>

      </div>

      <div class="row-bottom">

        <div class="hero">
          <div class="hero-top">
            <div class="hero-ico" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
              </svg>
            </div>
            <div>
              <div class="hero-title">CONSULTA RAPIDA DE ALUMNOS</div>
              <div class="hero-sub">NÚMERO DE CONTROL, NOMBRE O APELLIDO</div>
            </div>
          </div>
          <div class="hero-form">
            <input
              class="hero-input"
              v-model.trim="busquedaControl"
              placeholder="NÚMERO DE CONTROL, NOMBRE O APELLIDO"
              maxlength="100"
              type="text"
              aria-label="NUMERO DE CONTROL O NOMBRE DEL ALUMNO"
              @keydown.enter="irAKardex"
            />
            <button class="hero-btn" @click="irAKardex" :disabled="!esValidaBusqueda" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              BUSCAR
            </button>
          </div>
          <div class="hero-stats">
            <div class="hero-stat"><div class="hero-stat-n">{{ state.kpis.consultasHoy }}</div><div class="hero-stat-l">CONSULTAS HOY</div></div>
            <div class="hero-stat"><div class="hero-stat-n">{{ formatNum(state.kpis.totalAlumnos) }}</div><div class="hero-stat-l">ALUMNOS REGISTRADOS</div></div>
          </div>
        </div>

        <div class="card">
          <div class="ch"><span class="ct">ACCIONES RAPIDAS</span></div>
          <div class="acc-grid">
            <button
              v-for="acc in accionesRapidas"
              :key="acc.label"
              class="acc"
              :class="{ 'acc--prim': acc.primario }"
              @click="router.push(acc.ruta || '')"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path :d="acc.iconPath" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span class="acc-lbl">{{ acc.label?.toUpperCase() }}</span>
            </button>
          </div>
        </div>

        <div class="card">
          <div class="ch">
            <span class="ct">ESTADO DE INSCRIPCIONES</span>
            <span class="bdg bg-g" style="font-size:9px;font-weight:700;letter-spacing:.04em">ACTIVO</span>
          </div>
          <div class="ins-section">
            <div class="ins-row"><span class="ins-lbl">COMPLETADAS</span><span class="ins-val">{{ formatNum(state.kpis.inscripcionesCompletas) }} ({{ state.kpis.pctInscripciones }}%)</span></div>
            <div class="ins-bar"><div class="ins-fill" :style="{ width: state.kpis.pctInscripciones+'%', background:'#27AE60' }"></div></div>
            <div class="ins-row"><span class="ins-lbl">PENDIENTES</span><span class="ins-val" style="color:#F2994A">{{ formatNum(state.kpis.inscripcionesPendientes) }} ({{ 100-state.kpis.pctInscripciones }}%)</span></div>
            <div class="ins-bar"><div class="ins-fill" :style="{ width: (100-state.kpis.pctInscripciones)+'%', background:'#F2994A' }"></div></div>
          </div>
          <div class="ins-mini-grid">
            <div class="ins-mini"><div class="ins-mini-v">{{ state.kpis.numCarreras }}</div><div class="ins-mini-l">CARRERAS</div></div>
            <div class="ins-mini"><div class="ins-mini-v">3</div><div class="ins-mini-l">TURNOS</div></div>
            <div class="ins-mini"><div class="ins-mini-v">{{ state.kpis.gruposActivos }}</div><div class="ins-mini-l">GRUPOS</div></div>
            <div class="ins-mini"><div class="ins-mini-v">{{ state.kpis.pctInscripciones }}%</div><div class="ins-mini-l">COMPLETADO</div></div>
          </div>
        </div>

      </div>

      <Transition name="fade">
        <div v-if="state.error" class="alerta-error" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ state.error?.toUpperCase() }}
        </div>
      </Transition>

      <Teleport to="body">
        <Transition name="fade">
          <div v-if="modalItem" class="modal-overlay" @click.self="modalItem = null">
            <div class="modal-caja" role="dialog" aria-modal="true">
              <div class="modal-header">
                <h3 class="modal-titulo">DETALLE DE ACTIVIDAD</h3>
                <button class="modal-cerrar" @click="modalItem = null" aria-label="CERRAR">
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <div class="modal-body">
                <div class="modal-fila"><span class="modal-etiqueta">USUARIO</span><span class="modal-valor">{{ (modalItem.usuario || modalItem.nombre_usuario || '—').toUpperCase() }}</span></div>
                <div class="modal-fila"><span class="modal-etiqueta">ACCION</span><span class="bdg" :class="claseBadge(modalItem.accion)">{{ modalItem.accion?.toUpperCase() }}</span></div>
                <div class="modal-fila"><span class="modal-etiqueta">MODULO</span><span class="modal-valor">{{ (modalItem.nombre_modulo || '—').toUpperCase() }}</span></div>
                <div class="modal-fila"><span class="modal-etiqueta">IP</span><span class="modal-valor dd-mono">{{ modalItem.direccion_ip || '—' }}</span></div>
                <div class="modal-fila"><span class="modal-etiqueta">FECHA Y HORA</span><span class="modal-valor">{{ formatFecha(modalItem.fecha_hora) }}</span></div>
              </div>
              <div class="modal-footer">
                <button class="btn-modal-sec" @click="modalItem = null">CERRAR</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

// ── Importa tus componentes hijos ──────────────────────────────────────
import StatsGrid     from '@/components/dashboard/StatsGrid.vue'
import CareerFilters from '@/components/dashboard/CareerFilters.vue'
import CareerCard    from '@/components/dashboard/CareerCard.vue'

const router  = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Props ──────────────────────────────────────────────────────────────
const props = defineProps({
  busquedaGlobal: { type: String, default: '' }
})

// ── Estado UNIFICADO (el template usa state.xxx) ───────────────────────
const state = reactive({
  cargando:              true,
  cargandoBitacora:      false,
  error:                 null,
  errorBitacora:         false,
  carreras:              [],
  carreraActiva:         null,
  carreraData:           [],
  semestreData:          [],
  bitacora:              [],
  kpis: {
    totalAlumnos:           0,
    alumnosInscritos:       0,
    nuevosAlumnos:          0,
    inscripciones:          0,
    inscripcionesCompletas: 0,
    inscripcionesPendientes:0,
    pctInscripciones:       0,
    gruposActivos:          0,
    numCarreras:            0,
    materiasActivas:        0,
    adeudosPendientes:      0,
    egresados:              0,
    titulados:              0,
    bajasTemporales:        0,
    bajasDefinitivas:       0,
    consultasHoy:           0,
    periodoActivo:          'N/D',
  },
})

// ── Colores por carrera ────────────────────────────────────────────────
const COLORES_CARRERA = [
  '#1D52B7','#27AE60','#F2994A','#EB5757','#9B51E0','#2F80ED',
]

// ── Info usuario / fecha ───────────────────────────────────────────────
const nombreUsuario = ref('USUARIO')
const horaAcceso    = ref('')
const fechaCorta    = computed(() =>
  new Date().toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric' }).toUpperCase()
)
const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'BUENOS DÍAS'
  if (h < 19) return 'BUENAS TARDES'
  return 'BUENAS NOCHES'
})

watch(() => props.busquedaGlobal, (v) => {
  console.log('[Dashboard] Búsqueda global:', v)
})

// ── StatsGrid config ───────────────────────────────────────────────────
const statsConfig = computed(() => [
  {
    key:'alumnos', featured:true, label:'ALUMNOS ACTIVOS',
    valor: state.kpis.totalAlumnos, iconoTipo:'users', icoClass:'ico-azul',
    ruta:'/alumnos', linkLabel:'VER ALUMNOS',
    cambio:{ tipo:'up', texto:`+${state.kpis.nuevosAlumnos} VS ANTERIOR`, clase:'cambio-up' },
  },
  {
    key:'inscritos', label:'ALUMNOS INSCRITOS',
    valor: state.kpis.alumnosInscritos, iconoTipo:'list', icoClass:'ico-verde',
    ruta:'/inscripciones', linkLabel:'VER INSCRIPCIONES',
    cambio:{ tipo:'up', texto:`${state.kpis.pctInscripciones}% CON CALIFICACION`, clase:'cambio-up' },
  },
  {
    key:'grupos', label:'GRUPOS ABIERTOS',
    valor: state.kpis.gruposActivos, iconoTipo:'grid', icoClass:'ico-naranja',
    ruta:'/gestion-grupos', linkLabel:'VER GRUPOS',
    cambio:{ tipo:'ne', texto:`${state.kpis.numCarreras} CARRERAS · 3 TURNOS`, clase:'cambio-ne' },
  },
  {
    key:'materias', label:'MATERIAS ACTIVAS',
    valor: state.kpis.materiasActivas, iconoTipo:'book', icoClass:'ico-morado',
    ruta:null, linkLabel:null,
    cambio:{ tipo:'ne', texto:'EN EL PERIODO ACTUAL', clase:'cambio-ne' },
  },
  {
    key:'egresados', label:'EGRESADOS',
    valor: state.kpis.egresados, iconoTipo:'graduation', icoClass:'ico-verde',
    ruta:null, linkLabel:null,
    cambio:{ tipo:'up', texto:'PROCESO COMPLETADO', clase:'cambio-up' },
  },
  {
    key:'titulados', label:'TITULADOS',
    valor: state.kpis.titulados, iconoTipo:'graduation', icoClass:'ico-azul',
    ruta:null, linkLabel:null,
    cambio:{ tipo:'up', texto:'TITULO OBTENIDO', clase:'cambio-up' },
  },
  {
    key:'bajas-temp', label:'BAJAS TEMPORALES',
    valor: state.kpis.bajasTemporales, iconoTipo:'ban', icoClass:'ico-naranja',
    ruta:null, linkLabel:null,
    cambio:{ tipo:'dn', texto:'REQUIEREN SEGUIMIENTO', clase:'cambio-dn' },
  },
  {
    key:'bajas-def', label:'BAJAS DEFINITIVAS',
    valor: state.kpis.bajasDefinitivas, iconoTipo:'ban', icoClass:'ico-rojo',
    ruta:null, linkLabel:null,
    cambio:{ tipo:'dn', texto:'BAJA PERMANENTE', clase:'cambio-dn' },
  },
])

// ── Carreras filtradas ─────────────────────────────────────────────────
const carrerasMostradas = computed(() =>
  state.carreraActiva === null
    ? state.carreras
    : state.carreras.filter(c => c.id_carrera === state.carreraActiva)
)

const setCarreraActiva = (id) => {
  state.carreraActiva = state.carreraActiva === id ? null : id
}

const getIcono = (nombre = '') => {
  const n = nombre.toLowerCase()
  if (n.includes('sistem') || n.includes('comput')) return 'monitor'
  if (n.includes('industrial'))                      return 'settings'
  if (n.includes('electr'))                          return 'zap'
  if (n.includes('mecani'))                          return 'wrench'
  if (n.includes('empresa') || n.includes('gesti')) return 'briefcase'
  return 'flask'
}

// ── Gráfica semestres ──────────────────────────────────────────────────
const calcPctSem = (cant) => {
  const max = Math.max(...state.semestreData.map(s => s.cantidad), 1)
  return Math.round((cant / max) * 100)
}

// ── Helpers formato ────────────────────────────────────────────────────
const formatNum = (n) => Number(n || 0).toLocaleString('es-MX')

const claseBadge = (accion = '') => {
  const a = accion.toLowerCase()
  if (a.includes('insert') || a.includes('crear') || a.includes('nuevo')) return 'bdg bg-g'
  if (a.includes('update') || a.includes('edit')  || a.includes('modif')) return 'bdg bg-b'
  if (a.includes('delet')  || a.includes('elim'))                          return 'bdg bg-r'
  return 'bdg bg-a'
}

const tiempoRelativo = (fecha) => {
  if (!fecha) return '—'
  const diff = Date.now() - new Date(fecha).getTime()
  const min  = Math.floor(diff / 60000)
  if (min < 1)  return 'HACE UN MOMENTO'
  if (min < 60) return `HACE ${min} MIN`
  const h = Math.floor(min / 60)
  if (h  < 24)  return `HACE ${h} H`
  return `HACE ${Math.floor(h / 24)} DÍAS`
}

const formatFecha = (f) => {
  if (!f) return '—'
  return new Date(f).toLocaleString('es-MX', {
    year:'numeric', month:'long', day:'numeric',
    hour:'2-digit', minute:'2-digit',
  }).toUpperCase()
}

// ── Carga de datos ─────────────────────────────────────────────────────
const cargarDashboard = async () => {
  state.cargando = true
  state.error    = null
  try {
    const [resKpis, resCarreras, resSem] = await Promise.all([
      fetch(`${API_URL}/api/dashboard/kpis`).then(r => r.json()),
      fetch(`${API_URL}/api/dashboard/carreras`).then(r => r.json()),
      fetch(`${API_URL}/api/dashboard/semestres`).then(r => r.json()),
    ])
    Object.assign(state.kpis, resKpis.kpis ?? resKpis)
    state.carreras     = resCarreras.carreras    ?? resCarreras  ?? []
    state.carreraData  = resCarreras.carreraData ?? []
    state.semestreData = resSem.semestres        ?? resSem       ?? []
  } catch (e) {
    state.error = 'Error al cargar datos del dashboard'
    console.error(e)
  } finally {
    state.cargando = false
  }
}

const cargarBitacora = async () => {
  state.cargandoBitacora = true
  state.errorBitacora    = false
  try {
    const res = await fetch(`${API_URL}/api/bitacora?limit=8`)
    const data = await res.json()
    state.bitacora = data.registros ?? data ?? []
  } catch (e) {
    state.errorBitacora = true
    console.error(e)
  } finally {
    state.cargandoBitacora = false
  }
}

// ── Búsqueda ───────────────────────────────────────────────────────────
const busquedaControl = ref('')
const buscando        = ref(false)

const esValidaBusqueda = computed(() => {
  const t = busquedaControl.value.trim()
  if (!t) return false
  return /^\d+$/.test(t) ? t.length === 8 : t.length >= 3
})

const irAKardex = async () => {
  if (!esValidaBusqueda.value) return
  buscando.value = true
  state.error    = null
  try {
    const t = busquedaControl.value.trim()
    if (/^\d{8}$/.test(t)) {
      router.push(`/kardex/${t}`)
      return
    }
    const res  = await fetch(`${API_URL}/kardex/buscar-por-nombre?q=${encodeURIComponent(t)}`)
    const data = await res.json()
    const nc   = data.resultados?.[0]?.numero_control
    if (nc) router.push(`/kardex/${nc}`)
    else state.error = 'No se encontraron alumnos con ese nombre'
  } catch (e) {
    state.error = 'Error: ' + e.message
  } finally {
    buscando.value = false
  }
}

// ── Modal bitácora ─────────────────────────────────────────────────────
const modalItem = ref(null)

// ── Acciones rápidas ───────────────────────────────────────────────────
const accionesRapidas = [
  { label:'Nueva inscripción', primario:true,
    d:'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    ruta:'/formulario-alumno' },
  { label:'Lista de alumnos',
    d:'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    ruta:'/alumnos' },
  { label:'Lista de Carreras',
    d:'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
    ruta:'/gestion-academica/carreras' },
  { label:'Gestión de grupos',
    d:'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    ruta:'/gestion-grupos' },
  { label:'Cargar calificaciones',
    d:'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    ruta:'/calificaciones' },
]

// ── Lifecycle ──────────────────────────────────────────────────────────
onMounted(() => {
  cargarDashboard()
  cargarBitacora()
  horaAcceso.value = new Date().toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' })
  const u = JSON.parse(localStorage.getItem('usuario') || 'null')
  if (u?.nombre_usuario) nombreUsuario.value = u.nombre_usuario.toUpperCase()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   BASE
══════════════════════════════════════════════════════ */
.dashboard-page {
  font-family:'Montserrat',system-ui,sans-serif;
  background:#F4F6F9;
  display:flex; flex-direction:column; gap:14px;
  padding-bottom:2rem;
}

/* Barra carga */
.barra-carga { position:fixed; top:0; left:0; right:0; height:3px; z-index:9999; opacity:0; transition:opacity .2s; pointer-events:none; }
.barra-carga.activa { opacity:1; }
.barra-progreso { height:100%; background:#1D52B7; animation:progreso 1.5s ease-in-out infinite; }
@keyframes progreso { 0%{width:0%;opacity:1} 70%{width:85%;opacity:1} 100%{width:100%;opacity:0} }

/* Breadcrumb */
.bc { display:flex; align-items:center; gap:4px; font-size:10px; color:#828282; padding:0 2px; font-family:'Montserrat',sans-serif; font-weight:500; letter-spacing:.04em; }
.bc-sep { color:#BDBDBD; }
.bc b { color:#1D52B7; font-weight:700; }

/* Greeting */
.greeting { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
.gr-left h2 { font-size:24px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; margin:0 0 3px; letter-spacing:-.3px; }
.gr-left p  { font-size:11px; color:#4F4F4F; margin:0; letter-spacing:.03em; }
.periodo-pill { display:flex; align-items:center; gap:6px; background:#FFFFFF; border:1px solid #E0E0E0; border-radius:20px; padding:6px 14px; font-size:10px; font-family:'Montserrat',sans-serif; font-weight:500; letter-spacing:.04em; }
.p-dot  { width:7px; height:7px; border-radius:50%; background:#27AE60; flex-shrink:0; }
.p-text { color:#4F4F4F; }
.p-name { font-weight:700; color:#333333; }

/* ═══ SECCION CARRERAS ═══ */
.carreras-seccion {
  background:#FFFFFF; border:1px solid #E0E0E0; border-radius:14px;
  padding:20px; box-shadow:0 2px 8px rgba(29,82,183,0.05);
  display:flex; flex-direction:column; gap:16px;
}
.sec-header { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; flex-wrap:wrap; }
.sec-titulo-wrap { display:flex; flex-direction:column; gap:2px; }
.sec-titulo { font-size:16px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; margin:0; letter-spacing:.02em; }
.sec-sub    { font-size:11px; color:#828282; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }
.sec-link { font-size:10px; font-weight:700; color:#2F80ED; letter-spacing:.04em; text-decoration:none; display:flex; align-items:center; gap:3px; font-family:'Montserrat',sans-serif; transition:color .15s; white-space:nowrap; }
.sec-link:hover { color:#1A4184; }

/* Grid cards */
.carreras-wrap {
  display:grid; grid-template-columns:repeat(3,1fr); gap:14px;
}
.carreras-vacio { grid-column:1/-1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:3rem; color:#828282; font-size:11px; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }

/* Skeletons */
.cc-sk { background:#FFFFFF; border:1.5px solid #E0E0E0; border-radius:14px; overflow:hidden; animation:pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.55} }
.sk-barra  { height:4px; background:#F2F4F7; }
.sk-body   { display:flex; align-items:center; gap:10px; padding:15px 15px 8px; }
.sk-ico    { width:40px; height:40px; border-radius:10px; background:#F2F4F7; flex-shrink:0; }
.sk-lines  { flex:1; display:flex; flex-direction:column; gap:6px; }
.sk-line   { background:#F2F4F7; border-radius:4px; height:9px; }
.sk-lg { width:80%; } .sk-sm { width:38%; }
.sk-num   { height:32px; margin:0 15px 11px; background:#F2F4F7; border-radius:6px; width:55px; }
.sk-bar-h { height:7px; margin:0 15px 12px; background:#F2F4F7; border-radius:99px; }
.sk-chips { display:grid; grid-template-columns:1fr 1fr; gap:7px; padding:0 15px 15px; }
.sk-chip  { height:42px; background:#F2F4F7; border-radius:8px; }

/* ═══ GRAFICAS ═══ */
.row-graficas { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) 250px; gap:14px; }

/* Card base */
.card { background:#FFFFFF; border:1px solid #E0E0E0; border-radius:12px; padding:16px; box-shadow:0 2px 8px rgba(29,82,183,0.05); }
.card-bit { padding:14px; }
.ch   { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.ct   { font-size:13px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }
.cl   { font-size:10px; font-weight:600; color:#2F80ED; cursor:pointer; display:flex; align-items:center; gap:3px; text-decoration:none; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.cl:hover { color:#1A4184; }

/* Gráficas barras */
.grafica-barras { display:flex; flex-direction:column; gap:9px; }
.bar-r { display:flex; align-items:center; gap:8px; }
.bl    { font-size:10px; color:#4F4F4F; width:120px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-family:'Montserrat',sans-serif; font-weight:500; letter-spacing:.02em; }
.bt    { flex:1; height:6px; background:#F2F4F7; border-radius:3px; overflow:hidden; }
.bf    { height:100%; border-radius:3px; transition:width .6s ease; }
.bf-blue { background:#1D52B7; }
.bv    { font-size:10px; font-weight:700; color:#333333; min-width:28px; text-align:right; flex-shrink:0; font-family:'Montserrat',sans-serif; }
.ev    { display:flex; align-items:center; justify-content:center; padding:2rem; color:#828282; font-size:11px; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }

/* Bitácora */
.bit-loading { display:flex; align-items:center; gap:8px; padding:1rem 0; color:#828282; font-size:11px; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.spinner { width:15px; height:15px; border:2px solid #E0E0E0; border-top-color:#1D52B7; border-radius:50%; animation:girar .75s linear infinite; flex-shrink:0; }
@keyframes girar { to { transform:rotate(360deg); } }
.bit-item { display:flex; align-items:flex-start; gap:8px; padding:8px 4px; border-bottom:1px solid #F4F6F9; cursor:pointer; border-radius:6px; transition:background .12s; }
.bit-item:last-child { border-bottom:none; }
.bit-item:hover { background:#F4F6F9; }
.bit-av  { width:26px; height:26px; border-radius:50%; background:#0B2545; color:#FFFFFF; font-size:9px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-family:'Montserrat',sans-serif; }
.bit-av-gris { background:#BDBDBD; }
.bit-body { flex:1; min-width:0; }
.bit-r1   { display:flex; align-items:center; gap:5px; margin-bottom:2px; flex-wrap:wrap; }
.bit-usr  { font-size:10px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; letter-spacing:.02em; }
.bit-desc { font-size:9px; color:#4F4F4F; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-family:'Montserrat',sans-serif; letter-spacing:.02em; }
.bit-t    { font-size:9px; color:#828282; margin-top:1px; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }

/* Badges */
.bdg  { font-size:8px; font-weight:700; padding:2px 7px; border-radius:20px; font-family:'Montserrat',sans-serif; white-space:nowrap; letter-spacing:.04em; }
.bg-g { background:rgba(39,174,96,0.10);  color:#27AE60; }
.bg-b { background:rgba(47,128,237,0.10); color:#1D52B7; }
.bg-a { background:rgba(242,153,74,0.10); color:#F2994A; }
.bg-r { background:rgba(235,87,87,0.10);  color:#EB5757; }

/* ═══ FILA BOTTOM ═══ */
.row-bottom { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) 250px; gap:14px; }

/* Hero */
.hero        { background:#0B2545; border-radius:12px; padding:18px; display:flex; flex-direction:column; gap:12px; }
.hero-top    { display:flex; align-items:center; gap:9px; }
.hero-ico    { width:36px; height:36px; border-radius:9px; background:rgba(47,128,237,0.18); display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#2F80ED; }
.hero-ico svg { stroke:#2F80ED; }
.hero-title  { font-size:12px; font-weight:700; color:#FFFFFF; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.hero-sub    { font-size:10px; color:#828282; margin-top:2px; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }
.hero-form   { display:flex; gap:8px; }
.hero-input  { flex:1; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:9px 12px; font-size:11px; color:#FFFFFF; font-family:'Montserrat',sans-serif; outline:none; letter-spacing:.05em; }
.hero-input::placeholder { color:#828282; font-size:10px; letter-spacing:.03em; }
.hero-input:focus { border-color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.12); }
.hero-btn    { background:#1D52B7; border:none; border-radius:8px; padding:9px 14px; color:#FFFFFF; font-size:10px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:5px; font-family:'Montserrat',sans-serif; white-space:nowrap; transition:background .15s; letter-spacing:.05em; }
.hero-btn:hover:not(:disabled) { background:#1A4184; }
.hero-btn:disabled { opacity:.5; cursor:not-allowed; }
.hero-btn svg { stroke:#FFFFFF; }
.hero-stats  { display:flex; gap:8px; }
.hero-stat   { flex:1; background:rgba(255,255,255,0.05); border-radius:8px; padding:8px 12px; }
.hero-stat-n { font-size:18px; font-weight:700; color:#FFFFFF; font-family:'Montserrat',sans-serif; }
.hero-stat-l { font-size:9px; color:#828282; font-family:'Montserrat',sans-serif; letter-spacing:.04em; margin-top:1px; }

/* Acciones */
.acc-grid { display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-top:4px; }
.acc { display:flex; align-items:center; gap:8px; padding:10px 12px; border-radius:10px; border:1px solid #E0E0E0; background:#F2F4F7; cursor:pointer; font-family:'Montserrat',sans-serif; transition:all .12s; }
.acc:hover { border-color:#2F80ED; background:rgba(29,82,183,0.05); }
.acc--prim  { background:#0B2545; border-color:#0B2545; }
.acc--prim:hover { background:#1A4184; }
.acc svg   { stroke:#828282; flex-shrink:0; }
.acc--prim svg { stroke:#2F80ED; }
.acc-lbl   { font-size:10px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; text-align:left; letter-spacing:.04em; }
.acc--prim .acc-lbl { color:#FFFFFF; }

/* Inscripciones */
.ins-section { margin-bottom:12px; }
.ins-row   { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.ins-lbl   { font-size:10px; font-weight:600; color:#4F4F4F; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.ins-val   { font-size:11px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; }
.ins-bar   { height:5px; background:#F2F4F7; border-radius:3px; overflow:hidden; margin-bottom:10px; }
.ins-fill  { height:100%; border-radius:3px; transition:width .6s ease; }
.ins-mini-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.ins-mini  { background:#F4F6F9; border:1px solid #E0E0E0; border-radius:8px; padding:9px; text-align:center; }
.ins-mini-v { font-size:15px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; }
.ins-mini-l { font-size:9px; color:#828282; margin-top:2px; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }

/* Alerta */
.alerta-error { display:flex; align-items:center; gap:10px; background:#FFF0F0; border:1px solid rgba(235,87,87,0.20); border-radius:10px; padding:12px 16px; font-size:12px; color:#EB5757; font-weight:600; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }
.alerta-error svg { stroke:#EB5757; flex-shrink:0; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:2000; display:flex; align-items:center; justify-content:center; padding:1rem; }
.modal-caja    { background:#FFFFFF; border-radius:14px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.2); overflow:hidden; }
.modal-header  { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.5rem; border-bottom:1px solid #E0E0E0; background:#F4F6F9; }
.modal-titulo  { font-size:13px; font-weight:700; color:#333333; margin:0; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.modal-cerrar  { background:none; border:none; cursor:pointer; color:#828282; padding:4px; border-radius:6px; display:flex; align-items:center; transition:background .15s; }
.modal-cerrar:hover { background:#F2F4F7; }
.modal-body    { padding:1.3rem 1.5rem; display:flex; flex-direction:column; gap:.9rem; }
.modal-fila    { display:flex; flex-direction:column; gap:3px; }
.modal-etiqueta { font-size:9px; font-weight:700; color:#828282; text-transform:uppercase; letter-spacing:.07em; font-family:'Montserrat',sans-serif; }
.modal-valor   { font-size:13px; color:#333333; font-weight:600; font-family:'Montserrat',sans-serif; }
.dd-mono       { font-family:monospace; font-size:12px; }
.modal-footer  { padding:.9rem 1.5rem; border-top:1px solid #E0E0E0; display:flex; justify-content:flex-end; }
.btn-modal-sec { padding:7px 18px; background:#F2F4F7; color:#333333; border:none; border-radius:8px; font-weight:700; font-size:11px; cursor:pointer; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.btn-modal-sec:hover { background:#E0E0E0; }

/* Transiciones */
.fade-enter-active,.fade-leave-active { transition:opacity .25s; }
.fade-enter-from,.fade-leave-to { opacity:0; }

/* ══ RESPONSIVE ══ */
@media (max-width:1200px) {
  .carreras-wrap { grid-template-columns:repeat(2,1fr); }
  .row-graficas,.row-bottom { grid-template-columns:1fr 1fr; }
  .card-bit,.row-bottom .card:last-child { grid-column:1/-1; }
}
@media (max-width:768px) {
  .carreras-wrap { grid-template-columns:1fr; }
  .row-graficas,.row-bottom { grid-template-columns:1fr; }
  .greeting { flex-direction:column; align-items:flex-start; }
  .gr-left h2 { font-size:20px; }
  .acc-grid { grid-template-columns:1fr 1fr; }
}
@media (max-width:480px) {
  .acc-grid { grid-template-columns:1fr; }
}
</style>