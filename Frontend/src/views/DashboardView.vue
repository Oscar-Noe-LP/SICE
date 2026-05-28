<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="dashboard-page">

      <!-- ── Barra de carga top ── -->
      <div class="barra-carga" :class="{ activa: state.cargando }" aria-hidden="true">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── BREADCRUMB ── -->
      <div class="bc">
        <span>SICE</span>
        <ChevronRight :size="12" class="bc-sep" aria-hidden="true" />
        <b>INICIO</b>
      </div>

      <!-- ── GREETING ── -->
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

      <!-- ── KPI STATS GRID ── -->
      <StatsGrid
        :stats="statsConfig"
        :cargando="state.cargando"
        @navegar="router.push($event)"
      />

      <!-- ══════════════════════════════════════════
           SECCION CARRERAS
           Cards grandes + Filtros (lo que pide el plan)
      ══════════════════════════════════════════ -->
      <div class="carreras-seccion">

        <!-- Encabezado -->
        <div class="sec-header">
          <div class="sec-titulo-wrap">
            <h2 class="sec-titulo">CARRERAS</h2>
            <span class="sec-sub">RESUMEN POR CARRERA DEL PERIODO ACTUAL</span>
          </div>
          <router-link to="/gestion-grupos" class="sec-link">
            VER TODOS LOS GRUPOS
            <ChevronRight :size="13" aria-hidden="true" />
          </router-link>
        </div>

        <!-- ── FILTROS POR CARRERA CON ICONOS ── -->
        <CareerFilters
          :carreras="state.carreras"
          :carrera-activa="state.carreraActiva"
          :total-alumnos="state.kpis.totalAlumnos"
          @filtrar="setCarreraActiva"
        />

        <!-- ── CARDS GRANDES POR CARRERA ── -->
        <div class="carreras-wrap">

          <!-- Skeletons de carga -->
          <template v-if="state.cargando">
            <div v-for="i in 6" :key="'sk-'+i" class="cc-skeleton" aria-hidden="true">
              <div class="sk-barra"></div>
              <div class="sk-body">
                <div class="sk-ico"></div>
                <div class="sk-lines">
                  <div class="sk-line sk-lg"></div>
                  <div class="sk-line sk-sm"></div>
                </div>
              </div>
              <div class="sk-num"></div>
              <div class="sk-bar-h"></div>
              <div class="sk-chips">
                <div class="sk-chip"></div>
                <div class="sk-chip"></div>
              </div>
            </div>
          </template>

          <!-- Cards reales -->
          <template v-else>
            <TransitionGroup name="cc-fade">
              <CareerCard
                v-for="(carrera, i) in carrerasMostradas"
                :key="carrera.id_carrera"
                :carrera="carrera"
                :color="COLORES_CARRERA[i % COLORES_CARRERA.length]"
                :icono="getIconoLucide(carrera.nombre)"
                :activa="state.carreraActiva === carrera.id_carrera"
                @click="setCarreraActiva(carrera.id_carrera)"
              />
            </TransitionGroup>
          </template>

          <!-- Vacío -->
          <div v-if="!state.cargando && state.carreras.length === 0" class="carreras-vacio">
            <GraduationCap :size="40" color="#BDBDBD" aria-hidden="true" />
            <p>SIN DATOS DE CARRERAS DISPONIBLES</p>
          </div>

        </div>
      </div>

      <!-- ── FILA GRAFICAS + BITACORA ── -->
      <div class="row-graficas">

        <!-- Alumnos por carrera -->
        <div class="card">
          <div class="ch">
            <span class="ct">ALUMNOS POR CARRERA</span>
            <router-link to="/alumnos" class="cl">
              <ExternalLink :size="11" aria-hidden="true" /> VER DETALLE
            </router-link>
          </div>
          <div v-if="state.carreraData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in state.carreraData" :key="i" class="bar-r">
              <div class="bl" :title="item.carrera">{{ item.carrera }}</div>
              <div class="bt">
                <div class="bf" :style="{ width: item.porcentaje + '%', background: COLORES_CARRERA[i % COLORES_CARRERA.length] }"></div>
              </div>
              <div class="bv">{{ item.total }}</div>
            </div>
          </div>
          <div v-else class="ev">SIN DATOS DISPONIBLES</div>
        </div>

        <!-- Distribución por semestre -->
        <div class="card">
          <div class="ch">
            <span class="ct">DISTRIBUCION POR SEMESTRE</span>
            <span class="cl">ANALITICA</span>
          </div>
          <div v-if="state.semestreData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in state.semestreData" :key="i" class="bar-r">
              <div class="bl">{{ item.semestre }}° SEMESTRE</div>
              <div class="bt">
                <div class="bf bf-blue" :style="{ width: calcPctSem(item.cantidad) + '%' }"></div>
              </div>
              <div class="bv">{{ item.cantidad }}</div>
            </div>
          </div>
          <div v-else class="ev">SIN DATOS DISPONIBLES</div>
        </div>

        <!-- Bitácora -->
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
                {{ (item.usuario || item.nombre_usuario || '?').slice(0, 2).toUpperCase() }}
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
            <div class="bit-item" v-for="i in 3" :key="'ph-' + i">
              <div class="bit-av bit-av-gris" aria-hidden="true">?</div>
              <div class="bit-body">
                <div class="bit-desc" style="color:#BDBDBD">ESPERANDO CONEXION CON EL SERVIDOR...</div>
              </div>
            </div>
          </template>

          <div v-else class="ev">SIN ACTIVIDAD RECIENTE</div>
        </div>

      </div>

      <!-- ── FILA BUSQUEDA + ACCIONES + INSCRIPCIONES ── -->
      <div class="row-bottom">

        <!-- Hero búsqueda -->
        <div class="hero">
          <div class="hero-top">
            <div class="hero-ico" aria-hidden="true">
              <UserSearch :size="18" />
            </div>
            <div>
              <div class="hero-title">CONSULTA RAPIDA DE ALUMNO</div>
              <div class="hero-sub">NUMERO DE CONTROL → KARDEX COMPLETO AL INSTANTE</div>
            </div>
          </div>
          <div class="hero-form">
            <input
              class="hero-input"
              v-model.trim="busquedaControl"
              placeholder="EJ. 25000001"
              maxlength="8"
              inputmode="numeric"
              type="text"
              aria-label="NUMERO DE CONTROL DEL ALUMNO"
              @keydown.enter="irAKardex"
              @input="busquedaControl = busquedaControl.replace(/\D/g, '')"
            />
            <button
              class="hero-btn"
              @click="irAKardex"
              :disabled="busquedaControl.length < 8"
              type="button"
            >
              <Search :size="13" aria-hidden="true" />
              BUSCAR
            </button>
          </div>
          <div class="hero-stats">
            <div class="hero-stat">
              <div class="hero-stat-n">{{ state.kpis.consultasHoy }}</div>
              <div class="hero-stat-l">CONSULTAS HOY</div>
            </div>
            <div class="hero-stat">
              <div class="hero-stat-n">{{ formatNum(state.kpis.totalAlumnos) }}</div>
              <div class="hero-stat-l">ALUMNOS REGISTRADOS</div>
            </div>
          </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card">
          <div class="ch"><span class="ct">ACCIONES RAPIDAS</span></div>
          <div class="acc-grid">
            <button
              v-for="acc in accionesRapidas"
              :key="acc.label"
              class="acc"
              :class="{ 'acc--prim': acc.primario }"
              @click="router.push(acc.ruta)"
              type="button"
            >
              <component :is="acc.icono" :size="17" aria-hidden="true" />
              <span class="acc-lbl">{{ acc.label }}</span>
            </button>
          </div>
        </div>

        <!-- Estado inscripciones -->
        <div class="card">
          <div class="ch">
            <span class="ct">ESTADO DE INSCRIPCIONES</span>
            <span class="bdg bg-g" style="font-size:9px;font-weight:700;letter-spacing:.04em">ACTIVO</span>
          </div>
          <div class="ins-section">
            <div class="ins-row">
              <span class="ins-lbl">COMPLETADAS</span>
              <span class="ins-val">{{ formatNum(state.kpis.inscripcionesCompletas) }} ({{ state.kpis.pctInscripciones }}%)</span>
            </div>
            <div class="ins-bar">
              <div class="ins-fill" :style="{ width: state.kpis.pctInscripciones + '%', background: '#27AE60' }"></div>
            </div>
            <div class="ins-row">
              <span class="ins-lbl">PENDIENTES</span>
              <span class="ins-val" style="color:#F2994A">{{ formatNum(state.kpis.inscripcionesPendientes) }} ({{ 100 - state.kpis.pctInscripciones }}%)</span>
            </div>
            <div class="ins-bar">
              <div class="ins-fill" :style="{ width: (100 - state.kpis.pctInscripciones) + '%', background: '#F2994A' }"></div>
            </div>
          </div>
          <div class="ins-mini-grid">
            <div class="ins-mini"><div class="ins-mini-v">{{ state.kpis.numCarreras }}</div><div class="ins-mini-l">CARRERAS</div></div>
            <div class="ins-mini"><div class="ins-mini-v">3</div><div class="ins-mini-l">TURNOS</div></div>
            <div class="ins-mini"><div class="ins-mini-v">{{ state.kpis.gruposActivos }}</div><div class="ins-mini-l">GRUPOS</div></div>
            <div class="ins-mini"><div class="ins-mini-v">{{ state.kpis.pctInscripciones }}%</div><div class="ins-mini-l">COMPLETADO</div></div>
          </div>
        </div>

      </div>

      <!-- ── ALERTA ERROR ── -->
      <Transition name="fade">
        <div v-if="state.error" class="alerta-error" role="alert">
          <AlertCircle :size="20" aria-hidden="true" />
          {{ state.error?.toUpperCase() }}
        </div>
      </Transition>

      <!-- ── MODAL BITACORA ── -->
      <Teleport to="body">
        <Transition name="fade">
          <div v-if="modalItem" class="modal-overlay" @click.self="modalItem = null">
            <div class="modal-caja" role="dialog" aria-modal="true" aria-label="DETALLE DE ACTIVIDAD">
              <div class="modal-header">
                <h3 class="modal-titulo">DETALLE DE ACTIVIDAD</h3>
                <button class="modal-cerrar" @click="modalItem = null" aria-label="CERRAR">
                  <X :size="18" />
                </button>
              </div>
              <div class="modal-body">
                <div class="modal-fila">
                  <span class="modal-etiqueta">USUARIO</span>
                  <span class="modal-valor">{{ (modalItem.usuario || modalItem.nombre_usuario || '—').toUpperCase() }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">ACCION</span>
                  <span class="bdg" :class="claseBadge(modalItem.accion)">{{ modalItem.accion?.toUpperCase() }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">MODULO</span>
                  <span class="modal-valor">{{ (modalItem.nombre_modulo || '—').toUpperCase() }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">IP</span>
                  <span class="modal-valor dd-mono">{{ modalItem.direccion_ip || '—' }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">FECHA Y HORA</span>
                  <span class="modal-valor">{{ formatFecha(modalItem.fecha_hora) }}</span>
                </div>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  ChevronRight, ExternalLink, Search, UserSearch,
  AlertCircle, X, GraduationCap,
  Users, TrendingUp,
  Monitor, Settings, Zap, Wrench,
  Briefcase, FlaskConical, BookOpen,
  UserPlus, List, LayoutGrid,
  BarChart2, Building2,
} from 'lucide-vue-next'

import MainLayout    from '@/layouts/MainLayout.vue'
import StatsGrid     from '@/components/dashboard/StatsGrid.vue'
import CareerCard    from '@/components/dashboard/CareerCard.vue'
import CareerFilters from '@/components/dashboard/CareerFilters.vue'
import {
  dashboardState as state,
  cargarDashboard,
  cargarBitacora,
  setCarreraActiva,
  COLORES_CARRERA,
  formatNum,
  tiempoRelativo,
  claseBadge,
} from '@/stores/dashboardStore.js'

const router = useRouter()

// ── Saludo dinámico ──────────────────────────────────────────────────────────
const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'BUENOS DIAS'
  if (h < 19) return 'BUENAS TARDES'
  return 'BUENAS NOCHES'
})
const nombreUsuario = ref('USUARIO')
const horaAcceso    = ref('—')
const fechaCorta    = computed(() =>
  new Date().toLocaleDateString('es-MX', {
    weekday: 'short', day: 'numeric', month: 'short',
  }).toUpperCase()
)

// ── Configuración del StatsGrid ──────────────────────────────────────────────
const statsConfig = computed(() => [
  {
    key: 'alumnos', featured: true,
    label: 'TOTAL ALUMNOS',
    valor: state.kpis.totalAlumnos,
    icono: Users,
    icoClass: 'ico-azul',
    ruta: '/alumnos', linkLabel: 'VER ALUMNOS',
    cambio: { tipo: 'up', texto: `+${state.kpis.nuevosAlumnos} VS ANTERIOR`, clase: 'cambio-up' },
  },
  {
    key: 'inscripciones',
    label: 'INSCRIPCIONES',
    valor: state.kpis.inscripciones,
    icono: List,
    icoClass: 'ico-verde',
    ruta: '/inscripciones', linkLabel: 'VER INSCRIPCIONES',
    cambio: { tipo: 'up', texto: `${state.kpis.pctInscripciones}% COMPLETADAS`, clase: 'cambio-up' },
  },
  {
    key: 'grupos',
    label: 'GRUPOS ACTIVOS',
    valor: state.kpis.gruposActivos,
    icono: LayoutGrid,
    icoClass: 'ico-naranja',
    ruta: '/gestion-grupos', linkLabel: 'VER GRUPOS',
    cambio: { tipo: 'ne', texto: `${state.kpis.numCarreras} CARRERAS · 3 TURNOS`, clase: 'cambio-ne' },
  },
  {
    key: 'adeudos',
    label: 'ADEUDOS PENDIENTES',
    valor: state.kpis.adeudosPendientes,
    icono: AlertCircle,
    icoClass: 'ico-rojo',
    ruta: null, linkLabel: null,
    cambio: { tipo: 'dn', texto: 'REQUIEREN ATENCION', clase: 'cambio-dn' },
  },
])

// ── Cards de carrera filtradas ───────────────────────────────────────────────
const carrerasMostradas = computed(() =>
  state.carreraActiva === null
    ? state.carreras
    : state.carreras.filter(c => c.id_carrera === state.carreraActiva)
)

/** Devuelve el componente Lucide según el nombre de la carrera */
const getIconoLucide = (nombre = '') => {
  const n = nombre.toLowerCase()
  if (n.includes('sistem') || n.includes('comput')) return Monitor
  if (n.includes('industrial'))                      return Settings
  if (n.includes('electr'))                          return Zap
  if (n.includes('mecani') || n.includes('mecán'))   return Wrench
  if (n.includes('empresa') || n.includes('gesti'))  return Briefcase
  if (n.includes('bioqu'))                           return FlaskConical
  return BookOpen
}

// ── Gráficas ─────────────────────────────────────────────────────────────────
const calcPctSem = (cant) => {
  const max = Math.max(...state.semestreData.map(s => s.cantidad), 1)
  return Math.round((cant / max) * 100)
}

// ── Búsqueda rápida ───────────────────────────────────────────────────────────
const busquedaControl = ref('')
const irAKardex = () => {
  if (busquedaControl.value.length < 8) return
  router.push(`/kardex/${busquedaControl.value.trim()}`)
}

// ── Modal bitácora ────────────────────────────────────────────────────────────
const modalItem = ref(null)
const formatFecha = (fechaStr) => {
  if (!fechaStr) return '—'
  return new Date(fechaStr).toLocaleString('es-MX', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  }).toUpperCase()
}

// ── Acciones rápidas ──────────────────────────────────────────────────────────
const accionesRapidas = [
  { label: 'NUEVA INSCRIPCION', primario: true,  icono: UserPlus,  ruta: '/formulario-alumno' },
  { label: 'LISTA DE ALUMNOS',  primario: false, icono: Users,     ruta: '/alumnos'           },
  { label: 'GESTION DE GRUPOS', primario: false, icono: LayoutGrid,ruta: '/gestion-grupos'    },
  { label: 'CALIFICACIONES',    primario: false, icono: BarChart2, ruta: '/calificaciones'    },
  { label: 'GENERAR REPORTE',   primario: false, icono: List,      ruta: '/reportes'          },
  { label: 'COMITE ACADEMICO',  primario: false, icono: Building2, ruta: '/comite'            },
]

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  cargarDashboard()
  cargarBitacora()
  horaAcceso.value = new Date().toLocaleTimeString('es-MX', {
    hour: '2-digit', minute: '2-digit',
  })
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   BASE
══════════════════════════════════════════════════════ */
.dashboard-page {
  font-family: 'Montserrat', system-ui, sans-serif;
  background: #F4F6F9;
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding-bottom: 2rem;
}

/* ── Barra de carga ── */
.barra-carga { position:fixed; top:0; left:0; right:0; height:3px; z-index:9999; opacity:0; transition:opacity .2s; pointer-events:none; }
.barra-carga.activa { opacity:1; }
.barra-progreso { height:100%; background:#1D52B7; animation:progreso 1.5s ease-in-out infinite; }
@keyframes progreso { 0%{width:0%;opacity:1} 70%{width:85%;opacity:1} 100%{width:100%;opacity:0} }

/* ── Breadcrumb ── */
.bc { display:flex; align-items:center; gap:4px; font-size:10px; color:#828282; padding:0 2px; font-family:'Montserrat',sans-serif; font-weight:500; letter-spacing:.04em; }
.bc-sep { color:#BDBDBD; }
.bc b { color:#1D52B7; font-weight:700; }

/* ── Greeting ── */
.greeting { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
.gr-left h2 { font-size:24px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; margin:0 0 3px; letter-spacing:-.3px; }
.gr-left p  { font-size:11px; color:#4F4F4F; margin:0; letter-spacing:.03em; }
.periodo-pill { display:flex; align-items:center; gap:6px; background:#FFFFFF; border:1px solid #E0E0E0; border-radius:20px; padding:6px 14px; font-size:10px; font-family:'Montserrat',sans-serif; font-weight:500; letter-spacing:.04em; }
.p-dot  { width:7px; height:7px; border-radius:50%; background:#27AE60; flex-shrink:0; }
.p-text { color:#4F4F4F; }
.p-name { font-weight:700; color:#333333; }

/* ══ SECCION CARRERAS ══ */
.carreras-seccion {
  background: #FFFFFF;
  border: 1px solid #E0E0E0;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(29,82,183,0.05);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.sec-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
}

.sec-titulo-wrap { display:flex; flex-direction:column; gap:2px; }
.sec-titulo { font-size:16px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; margin:0; letter-spacing:.02em; }
.sec-sub    { font-size:11px; color:#828282; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }

.sec-link {
  font-size:10px; font-weight:700; color:#2F80ED; letter-spacing:.04em;
  text-decoration:none; display:flex; align-items:center; gap:3px;
  font-family:'Montserrat',sans-serif; transition:color .15s; white-space:nowrap;
}
.sec-link:hover { color:#1A4184; }

/* ── Grid de cards ── */
.carreras-wrap {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

/* Transition de entrada/salida de cards */
.cc-fade-enter-active { transition: all 0.3s ease; }
.cc-fade-leave-active { transition: all 0.2s ease; }
.cc-fade-enter-from   { opacity: 0; transform: translateY(8px); }
.cc-fade-leave-to     { opacity: 0; transform: scale(0.96); }

/* Estado vacío */
.carreras-vacio {
  grid-column: 1 / -1;
  display: flex; flex-direction:column; align-items:center; justify-content:center;
  gap: 10px; padding: 3rem; color: #828282; font-size: 11px;
  font-family:'Montserrat',sans-serif; letter-spacing:.04em;
}

/* Skeletons */
.cc-skeleton {
  background: #FFFFFF;
  border: 1.5px solid #E0E0E0;
  border-radius: 14px;
  overflow: hidden;
  animation: pulse 1.5s ease-in-out infinite;
}
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

/* ══ GRAFICAS ══ */
.row-graficas {
  display: grid;
  grid-template-columns: minmax(0,1fr) minmax(0,1fr) 250px;
  gap: 14px;
}

/* ── Card base ── */
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
.bit-r1  { display:flex; align-items:center; gap:5px; margin-bottom:2px; flex-wrap:wrap; }
.bit-usr  { font-size:10px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; letter-spacing:.02em; }
.bit-desc { font-size:9px; color:#4F4F4F; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-family:'Montserrat',sans-serif; letter-spacing:.02em; }
.bit-t    { font-size:9px; color:#828282; margin-top:1px; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }

/* Badges */
.bdg  { font-size:8px; font-weight:700; padding:2px 7px; border-radius:20px; font-family:'Montserrat',sans-serif; white-space:nowrap; letter-spacing:.04em; }
.bg-g { background:rgba(39,174,96,0.10);  color:#27AE60; }
.bg-b { background:rgba(47,128,237,0.10); color:#1D52B7; }
.bg-a { background:rgba(242,153,74,0.10); color:#F2994A; }
.bg-r { background:rgba(235,87,87,0.10);  color:#EB5757; }

/* ══ FILA BOTTOM ══ */
.row-bottom {
  display: grid;
  grid-template-columns: minmax(0,1fr) minmax(0,1fr) 250px;
  gap: 14px;
}

/* Hero búsqueda */
.hero        { background:#0B2545; border-radius:12px; padding:18px; display:flex; flex-direction:column; gap:12px; }
.hero-top    { display:flex; align-items:center; gap:9px; }
.hero-ico    { width:36px; height:36px; border-radius:9px; background:rgba(47,128,237,0.18); display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#2F80ED; }
.hero-title  { font-size:12px; font-weight:700; color:#FFFFFF; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.hero-sub    { font-size:10px; color:#828282; margin-top:2px; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }
.hero-form   { display:flex; gap:8px; }
.hero-input  { flex:1; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:9px 12px; font-size:11px; color:#FFFFFF; font-family:'Montserrat',sans-serif; outline:none; letter-spacing:.05em; }
.hero-input::placeholder { color:#828282; font-size:10px; letter-spacing:.03em; }
.hero-input:focus { border-color:rgba(255,255,255,0.45); background:rgba(255,255,255,0.12); }
.hero-btn    { background:#1D52B7; border:none; border-radius:8px; padding:9px 14px; color:#FFFFFF; font-size:10px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:5px; font-family:'Montserrat',sans-serif; white-space:nowrap; transition:background .15s; letter-spacing:.05em; }
.hero-btn:hover:not(:disabled) { background:#1A4184; }
.hero-btn:disabled { opacity:.5; cursor:not-allowed; }
.hero-stats  { display:flex; gap:8px; }
.hero-stat   { flex:1; background:rgba(255,255,255,0.05); border-radius:8px; padding:8px 12px; }
.hero-stat-n { font-size:18px; font-weight:700; color:#FFFFFF; font-family:'Montserrat',sans-serif; }
.hero-stat-l { font-size:9px; color:#828282; font-family:'Montserrat',sans-serif; letter-spacing:.04em; margin-top:1px; }

/* Acciones rápidas */
.acc-grid { display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-top:4px; }
.acc { display:flex; align-items:center; gap:8px; padding:10px 12px; border-radius:10px; border:1px solid #E0E0E0; background:#F2F4F7; cursor:pointer; font-family:'Montserrat',sans-serif; transition:all .12s; }
.acc:hover { border-color:#2F80ED; background:rgba(29,82,183,0.05); }
.acc--prim  { background:#0B2545; border-color:#0B2545; }
.acc--prim:hover { background:#1A4184; }
.acc svg, .acc :deep(svg) { flex-shrink:0; }
.acc     :deep(svg) { stroke:#828282; }
.acc--prim :deep(svg) { stroke:#2F80ED; }
.acc-lbl { font-size:10px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; text-align:left; letter-spacing:.04em; }
.acc--prim .acc-lbl { color:#FFFFFF; }

/* Estado inscripciones */
.ins-section { margin-bottom:12px; }
.ins-row     { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.ins-lbl     { font-size:10px; font-weight:600; color:#4F4F4F; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.ins-val     { font-size:11px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; }
.ins-bar     { height:5px; background:#F2F4F7; border-radius:3px; overflow:hidden; margin-bottom:10px; }
.ins-fill    { height:100%; border-radius:3px; transition:width .6s ease; }
.ins-mini-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.ins-mini    { background:#F4F6F9; border:1px solid #E0E0E0; border-radius:8px; padding:9px; text-align:center; }
.ins-mini-v  { font-size:15px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; }
.ins-mini-l  { font-size:9px; color:#828282; margin-top:2px; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }

/* ── Alerta ── */
.alerta-error { display:flex; align-items:center; gap:10px; background:#FFF0F0; border:1px solid rgba(235,87,87,0.20); border-radius:10px; padding:12px 16px; font-size:12px; color:#EB5757; font-weight:600; font-family:'Montserrat',sans-serif; letter-spacing:.03em; }

/* ── Modal ── */
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

/* ── Transiciones ── */
.fade-enter-active, .fade-leave-active { transition:opacity .25s; }
.fade-enter-from, .fade-leave-to { opacity:0; }

/* ══ RESPONSIVE ══ */
@media (max-width: 1200px) {
  .carreras-wrap { grid-template-columns: repeat(2, 1fr); }
  .row-graficas, .row-bottom { grid-template-columns: 1fr 1fr; }
  .card-bit { grid-column: 1 / -1; }
  .row-bottom .card:last-child { grid-column: 1 / -1; }
}
@media (max-width: 768px) {
  .carreras-wrap { grid-template-columns: 1fr; }
  .row-graficas, .row-bottom { grid-template-columns: 1fr; }
  .greeting { flex-direction: column; align-items: flex-start; }
  .gr-left h2 { font-size: 20px; }
  .acc-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
  .acc-grid { grid-template-columns: 1fr; }
}
</style>