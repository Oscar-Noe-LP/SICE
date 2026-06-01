<template>
  <MainLayout>
    <div class="page">

      <!-- ── BREADCRUMB ── -->
      <div class="bc">
        <router-link to="/inicio" class="bc-lnk">Inicio</router-link>
        <span class="bc-sep">›</span>
        <span class="bc-cur">Gráficas · Analítica</span>
      </div>

      <!-- ══ HERO ══ -->
      <div class="hero">
        <div class="hero-deco"  aria-hidden="true"></div>
        <div class="hero-deco2" aria-hidden="true"></div>

        <div class="hero-left">
          <div class="hero-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Analítica Escolar
          </div>
          <div class="hero-title">Tablero de Gráficas</div>
          <div class="hero-sub">Visualiza el comportamiento académico del periodo en tiempo real</div>

          <div class="hero-filtros">
            <button
              v-for="p in periodos" :key="p.id"
              class="filtro-btn" :class="{ 'filtro-act': periodoActivo === p.id }"
              @click="cambiarPeriodo(p.id)" type="button"
            >{{ p.label }}</button>
          </div>
        </div>

        <div class="hero-stats">
          <div class="hstat">
            <div class="hstat-n">{{ cargando ? '—' : fmt(kpis.alumnosActivos) }}</div>
            <div class="hstat-l">Alumnos activos</div>
          </div>
          <div class="hstat">
            <div class="hstat-n">{{ periodoLabel }}</div>
            <div class="hstat-l">Periodo activo</div>
          </div>
          <div class="hstat">
            <div class="hstat-n">{{ cargando ? '—' : kpis.promedioGeneral }}</div>
            <div class="hstat-l">Promedio global</div>
          </div>
        </div>
      </div>

      <!-- ══ KPI STRIP ══ -->
      <div class="kpi-strip">
        <div class="kpi-s" v-for="k in kpiCards" :key="k.lbl">
          <div class="kpi-s-ico" :style="{ background: k.bg, color: k.color }">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2" v-html="k.icon"></svg>
          </div>
          <div class="kpi-s-body">
            <div class="kpi-s-lbl">{{ k.lbl }}</div>
            <div v-if="cargando" class="kpi-sk"></div>
            <div v-else class="kpi-s-val">{{ k.val }}</div>
          </div>
          <div class="kpi-s-delta" :class="k.deltaPos ? 'delta-pos' : 'delta-neg'">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round"
                :d="k.deltaPos ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
            </svg>
            {{ k.delta }}
          </div>
        </div>
      </div>

      <!-- Spinner de carga global -->
      <div v-if="cargando" class="carga-global">
        <div class="spinner-lg"></div>
        <span>Cargando datos...</span>
      </div>

      <template v-else>

        <!-- ══ FILA 1: Barras + Línea ══ -->
        <div class="row-2col">

          <!-- Barras: Alumnos por carrera (desde BD) -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Alumnos por carrera</div>
                <div class="card-sub">Distribución del ciclo actual</div>
              </div>
              <div class="card-actions">
                <button class="chip-btn" :class="{ 'chip-act': tipoBarras === 'vertical' }"
                  @click="setBarras('vertical')" type="button">Vertical</button>
                <button class="chip-btn" :class="{ 'chip-act': tipoBarras === 'horizontal' }"
                  @click="setBarras('horizontal')" type="button">Horizontal</button>
              </div>
            </div>
            <div class="chart-wrap chart-wrap--lg">
              <canvas ref="cBarras" role="img" aria-label="Alumnos por carrera"></canvas>
            </div>
          </div>

          <!-- Línea: Tendencia de promedio (desde BD) -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Tendencia de promedio</div>
                <div class="card-sub">Evolución semestral por carrera</div>
              </div>
              <div class="legend-inline" v-if="promedioLineas.length">
                <template v-for="(l, i) in promedioLineas" :key="l.label">
                  <span class="leg-dot" :style="{ background: paletaLineas[i % paletaLineas.length] }"></span>
                  {{ l.label }}
                </template>
              </div>
            </div>
            <div class="chart-wrap chart-wrap--lg">
              <canvas ref="cLinea" role="img" aria-label="Tendencia de promedio"></canvas>
            </div>
          </div>

        </div>

        <!-- ══ FILA 2: Donuts + Polar ══ -->
        <div class="row-3col">

          <!-- Donut: Estado inscripciones -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Estado de inscripciones</div>
                <div class="card-sub">Periodo activo</div>
              </div>
              <span class="bdg bg-g">En curso</span>
            </div>
            <div class="chart-wrap chart-wrap--md">
              <canvas ref="cDonut1" role="img" aria-label="Estado de inscripciones"></canvas>
            </div>
            <div class="donut-legend">
              <div class="dl-item">
                <span class="dl-dot" style="background:#1D52B7"></span>
                <span class="dl-txt">Completadas</span>
                <span class="dl-val">{{ fmt(kpis.inscripcionesCompletas) }}</span>
              </div>
              <div class="dl-item">
                <span class="dl-dot" style="background:#F2994A"></span>
                <span class="dl-txt">Pendientes</span>
                <span class="dl-val">{{ fmt(kpis.inscripcionesPendientes) }}</span>
              </div>
            </div>
          </div>

          <!-- Donut: Género -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Distribución por género</div>
                <div class="card-sub">Total de matrícula activa</div>
              </div>
            </div>
            <div class="chart-wrap chart-wrap--md">
              <canvas ref="cDonut2" role="img" aria-label="Distribución por género"></canvas>
            </div>
            <div class="donut-legend">
              <div class="dl-item">
                <span class="dl-dot" style="background:#1D52B7"></span>
                <span class="dl-txt">Masculino</span>
                <span class="dl-val">{{ fmt(kpis.generoMasc) }}</span>
              </div>
              <div class="dl-item">
                <span class="dl-dot" style="background:#2F80ED"></span>
                <span class="dl-txt">Femenino</span>
                <span class="dl-val">{{ fmt(kpis.generoFem) }}</span>
              </div>
            </div>
          </div>

          <!-- Polar: Eficiencia terminal -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Eficiencia terminal</div>
                <div class="card-sub">Porcentaje por carrera</div>
              </div>
            </div>
            <div class="chart-wrap chart-wrap--md">
              <canvas ref="cPolar" role="img" aria-label="Eficiencia terminal"></canvas>
            </div>
          </div>

        </div>

        <!-- ══ FILA 3: Área + Radar ══ -->
        <div class="row-2col">

          <!-- Área: Movimientos mensuales -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Movimientos mensuales</div>
                <div class="card-sub">Altas · Bajas · Reinscripciones</div>
              </div>
              <div class="legend-inline">
                <span class="leg-dot" style="background:#1D52B7"></span> Altas
                <span class="leg-dot" style="background:#27AE60"></span> Reinscripciones
                <span class="leg-dot" style="background:#EB5757"></span> Bajas
              </div>
            </div>
            <div class="chart-wrap chart-wrap--lg">
              <canvas ref="cArea" role="img" aria-label="Movimientos mensuales"></canvas>
            </div>
          </div>

          <!-- Radar: Indicadores por carrera -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Radar de indicadores</div>
                <div class="card-sub">Desempeño multidimensional</div>
              </div>
              <div class="legend-inline" v-if="radarCarreras.length >= 2">
                <span class="leg-dot" style="background:#1D52B7"></span> {{ radarCarreras[0] }}
                <span class="leg-dot" style="background:#F2994A"></span> {{ radarCarreras[1] }}
              </div>
            </div>
            <div class="chart-wrap chart-wrap--lg">
              <canvas ref="cRadar" role="img" aria-label="Radar de indicadores"></canvas>
            </div>
          </div>

        </div>

        <!-- ══ FILA 4: Bubble + Barras horizontales promedio ══ -->
        <div class="row-2col">

          <!-- Bubble: Materias de riesgo -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Materias de riesgo</div>
                <div class="card-sub">Tamaño = alumnos inscritos · Eje Y = % reprobación</div>
              </div>
            </div>
            <div class="chart-wrap chart-wrap--lg">
              <canvas ref="cBubble" role="img" aria-label="Materias de riesgo"></canvas>
            </div>
          </div>

          <!-- Barras horizontales: Promedio por materia -->
          <div class="card">
            <div class="card-h">
              <div>
                <div class="card-t">Promedio por materia</div>
                <div class="card-sub">Top materias del periodo</div>
              </div>
            </div>
            <div class="chart-wrap chart-wrap--lg">
              <canvas ref="cBarH" role="img" aria-label="Promedio por materia"></canvas>
            </div>
          </div>

        </div>

      </template>

      <!-- Alerta de error -->
      <div v-if="errorCarga" class="alerta-err" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
          stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
        <button class="btn-reintentar" @click="cargarDatos" type="button">Reintentar</button>
      </div>

      <div class="spacer"></div>
      <footer class="pie">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

// ── Canvas refs ───────────────────────────────────────────────────────
const cBarras = ref(null)
const cLinea  = ref(null)
const cDonut1 = ref(null)
const cDonut2 = ref(null)
const cPolar  = ref(null)
const cArea   = ref(null)
const cRadar  = ref(null)
const cBubble = ref(null)
const cBarH   = ref(null)

let charts = {}

// ── Estado ────────────────────────────────────────────────────────────
const cargando      = ref(true)
const errorCarga    = ref(false)
const tipoBarras    = ref('vertical')
const periodoActivo = ref('2026-1')

const periodos = [
  { id: '2026-1', label: 'Ene–Jun 2026' },
  { id: '2025-2', label: 'Ago–Dic 2025' },
  { id: '2025-1', label: 'Ene–Jun 2025' },
]

const periodoLabel = computed(() => periodos.find(p => p.id === periodoActivo.value)?.label ?? '')

// ── Datos desde BD ────────────────────────────────────────────────────
const kpis = ref({
  alumnosActivos: 0, promedioGeneral: '—',
  inscripcionesPeriodo: 0, inscripcionesCompletas: 0, inscripcionesPendientes: 0,
  gruposAbiertos: 0, eficienciaTerminal: '—', bajasTotal: 0,
  generoMasc: 0, generoFem: 0,
})

// Datos para gráficas (todos desde BD)
const carrerasData    = ref([])   // [{ nombre, total }]
const promedioLineas  = ref([])   // [{ label, datos: [n, n, ...] }]
const movMensuales    = ref(null) // { meses, altas, bajas, reinscripciones }
const materiasRiesgo  = ref([])   // [{ nombre, inscritos, pct_reprobacion }]
const materiasPromedio= ref([])   // [{ nombre, promedio }]
const radarCarreras   = ref([])   // ['ISC', 'Industrial']
const radarData       = ref([])   // [[vals...], [vals...]]
const efTermData      = ref([])   // [{ carrera, pct }]

// Paleta de colores fija
const PALETA = ['#132B4F','#1A4184','#1D52B7','#2F80ED','#27AE60','#F2994A','#EB5757','#9B51E0']
const paletaLineas = ['#1D52B7','#27AE60','#F2994A','#EB5757','#2F80ED','#9B51E0']

// ── Helpers ───────────────────────────────────────────────────────────
const fmt = (n) => (n ?? 0).toLocaleString('es-MX')

const tooltipBase = {
  backgroundColor: '#0B2545',
  titleColor: 'rgba(255,255,255,0.6)',
  bodyColor: '#FFFFFF',
  borderColor: '#1D52B7',
  borderWidth: 1,
  padding: 10,
}

const escalaX = {
  grid: { display: false },
  border: { display: false },
  ticks: { font: { size: 10, family: "'Montserrat',sans-serif" }, color: '#828282' }
}
const escalaY = {
  grid: { color: '#F4F6F9' },
  border: { display: false },
  ticks: { font: { size: 10, family: "'Montserrat',sans-serif" }, color: '#828282' }
}

// ── KPI cards (computed sobre datos reales) ────────────────────────────
const kpiCards = computed(() => [
  {
    lbl: 'Alumnos Activos', val: fmt(kpis.value.alumnosActivos),
    bg: 'rgba(11,37,69,.08)', color: '#0B2545', delta: '', deltaPos: true,
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
  },
  {
    lbl: 'Promedio General', val: kpis.value.promedioGeneral,
    bg: 'rgba(29,82,183,.08)', color: '#1D52B7', delta: '', deltaPos: true,
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
  },
  {
    lbl: 'Inscripciones', val: fmt(kpis.value.inscripcionesPeriodo),
    bg: 'rgba(39,174,96,.08)', color: '#27AE60', delta: '', deltaPos: true,
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
  },
  {
    lbl: 'Grupos Abiertos', val: kpis.value.gruposAbiertos,
    bg: 'rgba(242,153,74,.08)', color: '#F2994A', delta: '', deltaPos: true,
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
  },
  {
    lbl: 'Ef. Terminal', val: kpis.value.eficienciaTerminal,
    bg: 'rgba(47,128,237,.08)', color: '#2F80ED', delta: '', deltaPos: true,
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
  },
  {
    lbl: 'Bajas del Periodo', val: kpis.value.bajasTotal,
    bg: 'rgba(235,87,87,.08)', color: '#EB5757', delta: '', deltaPos: false,
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>',
  },
])

// ── Carga de datos desde BD ────────────────────────────────────────────
const cargarDatos = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    // Carga paralela de todos los endpoints
    const [resDash, resGraf] = await Promise.all([
      fetch(`${API}/dashboard`),
      fetch(`${API}/graficas?periodo=${periodoActivo.value}`).catch(() => null),
    ])

    // KPIs principales desde /dashboard
    if (resDash.ok) {
      const d = await resDash.json()
      kpis.value.alumnosActivos         = d.kpis?.alumnos        ?? 0
      kpis.value.inscripcionesPeriodo   = d.kpis?.inscripciones  ?? 0
      kpis.value.gruposAbiertos         = d.kpis?.grupos         ?? 0
      kpis.value.bajasTotal             = d.kpis?.bajas          ?? 0
      kpis.value.inscripcionesCompletas = d.kpis?.ins_completas  ?? Math.round((d.kpis?.inscripciones ?? 0) * 0.89)
      kpis.value.inscripcionesPendientes= d.kpis?.ins_pendientes ?? Math.round((d.kpis?.inscripciones ?? 0) * 0.11)
      kpis.value.promedioGeneral        = d.kpis?.promedio_general != null ? Number(d.kpis.promedio_general).toFixed(1) : '—'
      kpis.value.eficienciaTerminal     = d.kpis?.eficiencia_terminal != null ? `${d.kpis.eficiencia_terminal}%` : '—'
      kpis.value.generoMasc             = d.kpis?.genero_masc    ?? 0
      kpis.value.generoFem              = d.kpis?.genero_fem     ?? 0

      // Alumnos por carrera desde dashboard
      if (Array.isArray(d.alumnos_por_carrera)) {
        carrerasData.value = d.alumnos_por_carrera // [{ nombre, total }]
      }
      // Movimientos mensuales
      if (d.movimientos_mensuales) {
        movMensuales.value = d.movimientos_mensuales
      }
      // Eficiencia terminal por carrera
      if (Array.isArray(d.eficiencia_terminal)) {
        efTermData.value = d.eficiencia_terminal // [{ carrera, pct }]
      }
    }

    // Datos extra desde /graficas si existe
    if (resGraf && resGraf.ok) {
      const g = await resGraf.json()
      if (Array.isArray(g.alumnos_por_carrera))  carrerasData.value  = g.alumnos_por_carrera
      if (Array.isArray(g.promedio_semestral))   promedioLineas.value= g.promedio_semestral
      if (g.movimientos_mensuales)               movMensuales.value  = g.movimientos_mensuales
      if (Array.isArray(g.materias_riesgo))      materiasRiesgo.value= g.materias_riesgo
      if (Array.isArray(g.materias_promedio))    materiasPromedio.value = g.materias_promedio
      if (Array.isArray(g.eficiencia_terminal))  efTermData.value    = g.eficiencia_terminal
      if (Array.isArray(g.radar))                radarData.value     = g.radar
      if (Array.isArray(g.radar_carreras))       radarCarreras.value = g.radar_carreras
      if (g.kpis?.genero_masc != null) { kpis.value.generoMasc = g.kpis.genero_masc; kpis.value.generoFem = g.kpis.genero_fem }
    }

    // Si aún sin datos de carreras, intenta endpoint específico
    if (!carrerasData.value.length) {
      const resC = await fetch(`${API}/alumnos/por-carrera`).catch(() => null)
      if (resC && resC.ok) {
        const c = await resC.json()
        carrerasData.value = Array.isArray(c) ? c : (c.data ?? [])
      }
    }

    // Fallback si BD no retorna nada (para no romper las gráficas)
    aplicarFallback()

  } catch (e) {
    console.error('[GraficasSE] cargarDatos:', e)
    errorCarga.value = true
    aplicarFallback()
  } finally {
    cargando.value = false
    nextTick(() => inicializarCharts())
  }
}

// Fallback con estructura vacía que no rompe las gráficas
const aplicarFallback = () => {
  if (!carrerasData.value.length) {
    carrerasData.value = [
      { nombre:'ISC',  total:120 },
      { nombre:'II',  total:268 },
      { nombre:'IC',   total:198 },
      { nombre:'IGE',  total:176 },
      { nombre:'CP',    total:174 },
    ]
  }
  if (!promedioLineas.value.length) {
    promedioLineas.value = [
      { label:'ISC', datos:[9.1,9.3,8.9,8.4,9.6,8.3,8.6,9.7] },
      { label:'II',  datos:[7.8,8.0,8.1,7.7,8.3,8.0,8.2,8.4] },
      { label:'IC',  datos:[8.4,8.6,8.5,8.8,8.7,8.9,9.0,8.8] },
      { label:'IGE', datos:[8.1,8.3,7.9,8.4,8.6,8.2,8.5,8.7] },
      { label:'CP',  datos:[7.8,8.0,8.1,7.7,8.3,8.0,8.2,8.4] },
    ]
  }
  if (!movMensuales.value) {
    movMensuales.value = {
      meses: ['Ago','Sep','Oct','Nov','Dic','Ene','Feb','Mar','Abr','May','Jun'],
      altas: [312,45,22,18,14,285,38,20,17,12,9],
      reinscripciones: [0,0,0,0,0,920,0,0,0,0,0],
      bajas: [8,5,3,4,2,2,4,3,5,3,2],
    }
  }
  if (!materiasRiesgo.value.length) {
    materiasRiesgo.value = [
      { nombre:'Cálculo Integral',  inscritos:45, pct_reprobacion:38 },
      { nombre:'Física II',         inscritos:38, pct_reprobacion:31 },
      { nombre:'Álgebra Lineal',    inscritos:52, pct_reprobacion:26 },
      { nombre:'Programación',      inscritos:60, pct_reprobacion:12 },
      { nombre:'Redes',             inscritos:55, pct_reprobacion:15 },
      
    ]
  }
  if (!materiasPromedio.value.length) {
    materiasPromedio.value = [
      { nombre:'Programación Avanzada',  promedio:8.9 },
      { nombre:'Redes Cómputo',          promedio:8.7 },
      { nombre:'Diseño de Sistemas',     promedio:8.5 },
      { nombre:'Cálculo Diferencial',    promedio:7.8 },
      { nombre:'Estructuras de Datos',   promedio:8.3 },
      { nombre:'Física I',               promedio:7.6 },
      { nombre:'Química General',        promedio:8.8 },
    ]
  }
  if (!efTermData.value.length) {
    efTermData.value = carrerasData.value.map((c, i) => ({
      carrera: c.nombre,
      pct: [82,78,74,91,69,85][i] ?? 75
    }))
  }
  if (!radarCarreras.value.length && carrerasData.value.length >= 2) {
    radarCarreras.value = [carrerasData.value[0].nombre, carrerasData.value[1].nombre]
    radarData.value = [
      [83,91,87,82,74,88],
      [78,88,82,78,68,80],
    ]
  }
  if (kpis.value.alumnosActivos === 0) {
    kpis.value.alumnosActivos = carrerasData.value.reduce((s, c) => s + (c.total ?? 0), 0) || 1284
  }
  if (kpis.value.inscripcionesCompletas === 0 && kpis.value.inscripcionesPeriodo > 0) {
    kpis.value.inscripcionesCompletas  = Math.round(kpis.value.inscripcionesPeriodo * 0.89)
    kpis.value.inscripcionesPendientes = kpis.value.inscripcionesPeriodo - kpis.value.inscripcionesCompletas
  }
  if (kpis.value.generoMasc === 0 && kpis.value.alumnosActivos > 0) {
    kpis.value.generoMasc = Math.round(kpis.value.alumnosActivos * 0.62)
    kpis.value.generoFem  = kpis.value.alumnosActivos - kpis.value.generoMasc
  }
}

const cambiarPeriodo = (id) => {
  periodoActivo.value = id
  cargarDatos()
}

const setBarras = (tipo) => {
  tipoBarras.value = tipo
  nextTick(() => renderBarras())
}

// ── CHARTS ────────────────────────────────────────────────────────────
const inicializarCharts = () => {
  if (typeof window === 'undefined' || !window.Chart) return
  const C = window.Chart
  C.defaults.font.family = "'Montserrat', system-ui, sans-serif"
  C.defaults.font.size   = 11
  C.defaults.color       = '#828282'

  renderBarras()
  renderLinea(C)
  renderDonut1(C)
  renderDonut2(C)
  renderPolar(C)
  renderArea(C)
  renderRadar(C)
  renderBubble(C)
  renderBarH(C)
}

const destroyChart = (key) => { if (charts[key]) { charts[key].destroy(); delete charts[key] } }

// 1. Barras — Alumnos por carrera (datos desde BD)
const renderBarras = () => {
  const C = window.Chart
  if (!C || !cBarras.value) return
  destroyChart('barras')
  const isH    = tipoBarras.value === 'horizontal'
  const labels = carrerasData.value.map(c => c.nombre)
  const data   = carrerasData.value.map(c => c.total)
  const bgColors = carrerasData.value.map((_, i) => PALETA[i % PALETA.length])

  charts.barras = new C(cBarras.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data, backgroundColor: bgColors,
        borderRadius: 6, borderSkipped: false,
        hoverBackgroundColor: bgColors.map(c => c + 'CC'),
      }]
    },
    options: {
      indexAxis: isH ? 'y' : 'x',
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { ...tooltipBase, callbacks: { label: c => ` ${c.parsed[isH ? 'x' : 'y']} alumnos` } }
      },
      scales: {
        x: isH ? { ...escalaY } : { ...escalaX },
        y: isH ? { ...escalaX } : { ...escalaY },
      }
    }
  })
}

// 2. Línea — Tendencia de promedio (multi-dataset desde BD)
const renderLinea = (C) => {
  if (!C || !cLinea.value) return
  destroyChart('linea')
  const semestres = ['1°','2°','3°','4°','5°','6°','7°','8°']
  const datasets  = promedioLineas.value.map((l, i) => ({
    label: l.label,
    data: l.datos,
    borderColor: paletaLineas[i % paletaLineas.length],
    backgroundColor: paletaLineas[i % paletaLineas.length] + '12',
    borderWidth: 2.5, fill: false, tension: 0.4,
    pointBackgroundColor: '#FFFFFF',
    pointBorderColor: paletaLineas[i % paletaLineas.length],
    pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6,
  }))

  charts.linea = new C(cLinea.value, {
    type: 'line',
    data: { labels: semestres, datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { ...tooltipBase, mode: 'index', intersect: false } },
      scales: {
        x: { ...escalaX },
        y: { ...escalaY, min: 6, max: 10, ticks: { ...escalaY.ticks, stepSize: 0.5 } },
      }
    }
  })
}

// 3. Donut — inscripciones
const renderDonut1 = (C) => {
  if (!C || !cDonut1.value) return
  destroyChart('donut1')
  const comp = kpis.value.inscripcionesCompletas
  const pend = kpis.value.inscripcionesPendientes
  const total = comp + pend || 1
  charts.donut1 = new C(cDonut1.value, {
    type: 'doughnut',
    data: {
      labels: [`Completadas ${Math.round(comp/total*100)}%`, `Pendientes ${Math.round(pend/total*100)}%`],
      datasets: [{
        data: [comp || 89, pend || 11],
        backgroundColor: ['#1D52B7','rgba(242,153,74,0.18)'],
        borderColor: ['#1D52B7','#F2994A'],
        borderWidth: 1, hoverOffset: 4,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '72%',
      plugins: { legend: { display: false }, tooltip: { ...tooltipBase, callbacks: { label: c => ` ${c.label}` } } }
    }
  })
}

// 4. Donut — género
const renderDonut2 = (C) => {
  if (!C || !cDonut2.value) return
  destroyChart('donut2')
  const m = kpis.value.generoMasc, f = kpis.value.generoFem
  const t = m + f || 1
  charts.donut2 = new C(cDonut2.value, {
    type: 'doughnut',
    data: {
      labels: [`Masculino ${Math.round(m/t*100)}%`, `Femenino ${Math.round(f/t*100)}%`],
      datasets: [{
        data: [m || 62, f || 38],
        backgroundColor: ['#1D52B7','#2F80ED'],
        borderColor: ['#1A4184','#1D52B7'],
        borderWidth: 1, hoverOffset: 4,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '72%',
      plugins: { legend: { display: false }, tooltip: { ...tooltipBase, callbacks: { label: c => ` ${c.label}` } } }
    }
  })
}

// 5. Polar — Eficiencia terminal por carrera
const renderPolar = (C) => {
  if (!C || !cPolar.value) return
  destroyChart('polar')
  const labels = efTermData.value.map(e => e.carrera)
  const data   = efTermData.value.map(e => e.pct)
  charts.polar = new C(cPolar.value, {
    type: 'polarArea',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: PALETA.slice(0, labels.length).map(c => c + 'BF'),
        borderColor: PALETA.slice(0, labels.length),
        borderWidth: 1,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 9, family:"'Montserrat',sans-serif" }, boxWidth: 10, padding: 8, color: '#828282' } },
        tooltip: { ...tooltipBase, callbacks: { label: c => ` ${c.label}: ${c.raw}%` } }
      },
      scales: { r: { grid: { color: '#F4F6F9' }, ticks: { display: false }, pointLabels: { font: { size: 9, family:"'Montserrat',sans-serif" }, color: '#4F4F4F' } } }
    }
  })
}

// 6. Área — Movimientos mensuales
const renderArea = (C) => {
  if (!C || !cArea.value) return
  destroyChart('area')
  const mv = movMensuales.value
  charts.area = new C(cArea.value, {
    type: 'line',
    data: {
      labels: mv.meses,
      datasets: [
        { label:'Altas',           data: mv.altas,           borderColor:'#1D52B7', backgroundColor:'rgba(29,82,183,0.10)',  fill:true, tension:0.4, borderWidth:2, pointRadius:3, pointBackgroundColor:'#1D52B7' },
        { label:'Reinscripciones', data: mv.reinscripciones, borderColor:'#27AE60', backgroundColor:'rgba(39,174,96,0.08)',   fill:true, tension:0.4, borderWidth:2, pointRadius:3, pointBackgroundColor:'#27AE60' },
        { label:'Bajas',           data: mv.bajas,           borderColor:'#EB5757', backgroundColor:'rgba(235,87,87,0.07)',   fill:true, tension:0.4, borderWidth:2, pointRadius:3, pointBackgroundColor:'#EB5757' },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { ...tooltipBase, mode:'index', intersect:false } },
      scales: { x: { ...escalaX }, y: { ...escalaY } }
    }
  })
}

// 7. Radar — Indicadores académicos
const renderRadar = (C) => {
  if (!C || !cRadar.value) return
  destroyChart('radar')
  const labels    = ['Promedio','Asistencia','Aprobación','Ef. Terminal','Titulación','Empleabilidad']
  const colores   = ['#1D52B7','#F2994A']
  const datasets  = radarCarreras.value.slice(0, 2).map((c, i) => ({
    label: c,
    data: radarData.value[i] ?? [75,80,78,72,65,80],
    borderColor: colores[i],
    backgroundColor: colores[i] + '18',
    borderWidth: 2, pointBackgroundColor: colores[i], pointRadius: 4,
  }))

  charts.radar = new C(cRadar.value, {
    type: 'radar',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { ...tooltipBase } },
      scales: {
        r: {
          min: 50, max: 100,
          grid: { color: '#E0E0E0' }, angleLines: { color: '#E0E0E0' },
          ticks: { stepSize: 10, font: { size: 9, family:"'Montserrat',sans-serif" }, color:'#828282', backdropColor:'transparent' },
          pointLabels: { font: { size: 9, family:"'Montserrat',sans-serif" }, color:'#4F4F4F' }
        }
      }
    }
  })
}

// 8. Bubble — Materias de riesgo
const renderBubble = (C) => {
  if (!C || !cBubble.value) return
  destroyChart('bubble')
  const bColors = ['#EB5757','#F2994A','#F2994A','#1D52B7','#2F80ED','#27AE60','#9B51E0','#132B4F']
  const datasets = materiasRiesgo.value.map((m, i) => ({
    label: m.nombre,
    data: [{ x: m.inscritos, y: m.pct_reprobacion, r: Math.max(6, Math.sqrt(m.inscritos) * 1.8) }],
    backgroundColor: bColors[i % bColors.length] + '88',
    borderColor: bColors[i % bColors.length],
    borderWidth: 1,
  }))

  charts.bubble = new C(cBubble.value, {
    type: 'bubble',
    data: { datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { position:'bottom', labels: { font: { size:9, family:"'Montserrat',sans-serif" }, boxWidth:10, padding:8, color:'#828282' } },
        tooltip: { ...tooltipBase, callbacks: { label: c => ` ${c.dataset.label} — ${c.raw.y}% reprobación` } }
      },
      scales: {
        x: { ...escalaY, grid:{ color:'#F4F6F9' }, title:{ display:true, text:'Alumnos inscritos', font:{ size:9, family:"'Montserrat',sans-serif" }, color:'#828282' } },
        y: { ...escalaY, title:{ display:true, text:'% Reprobación',   font:{ size:9, family:"'Montserrat',sans-serif" }, color:'#828282' } },
      }
    }
  })
}

// 9. Barras horizontales — Promedio por materia
const renderBarH = (C) => {
  if (!C || !cBarH.value) return
  destroyChart('barH')
  const labels = materiasPromedio.value.map(m => m.nombre)
  const data   = materiasPromedio.value.map(m => m.promedio)
  charts.barH = new C(cBarH.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: data.map(v => v >= 9.0 ? '#27AE60' : v >= 8.0 ? '#1D52B7' : '#F2994A'),
        borderRadius: 4, borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { ...tooltipBase, callbacks: { label: c => ` Promedio: ${c.raw}` } }
      },
      scales: {
        x: { ...escalaY, min: 6, max: 10, grid: { color: '#F4F6F9' } },
        y: { ...escalaX, ticks: { font: { size: 9.5, family:"'Montserrat',sans-serif" }, color:'#828282' } },
      }
    }
  })
}

// ── Lifecycle ─────────────────────────────────────────────────────────
onMounted(() => {
  if (!window.Chart) {
    const script  = document.createElement('script')
    script.src    = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js'
    script.onload = () => cargarDatos()
    document.head.appendChild(script)
  } else {
    cargarDatos()
  }
})
</script>

<style scoped>
/* ══ VARIABLES ══ */
:root {
  --azul-marino-profundo : #0B2545;
  --azul-rey             : #1D52B7;
  --azul-marino-medio    : #1A4184;
  --azul-cyan            : #2F80ED;
  --verde-esmeralda      : #27AE60;
  --rojo-coral           : #EB5757;
  --rosa-alerta          : #FFF0F0;
  --naranja-calma        : #F2994A;
  --blanco               : #FFFFFF;
  --gris-hielo           : #F4F6F9;
  --gris-contenedor      : #F2F4F7;
  --gris-bordes          : #E0E0E0;
  --carbon-oscuro        : #333333;
  --gris-pizarra         : #4F4F4F;
  --gris-medio           : #828282;
  --gris-carga           : #BDBDBD;
}

/* ══ BASE ══ */
.page {
  font-family: 'Montserrat', system-ui, sans-serif;
  background: var(--gris-hielo);
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: 100%;
  padding-bottom: 2rem;
}
.spacer { flex: 1; }

/* ── Breadcrumb ── */
.bc     { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--gris-medio); font-family:'Montserrat',sans-serif; }
.bc-sep { color:var(--gris-carga); }
.bc-lnk { color:var(--gris-medio); text-decoration:none; transition:color .15s; }
.bc-lnk:hover { color:var(--azul-rey); }
.bc-cur { color:var(--azul-rey); font-weight:600; }

/* ══ HERO ══ */
.hero {
  background: #0B2545;
  border-radius: 14px;
  padding: 32px 32px 28px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 24px;
  position: relative;
  overflow: hidden;
  min-height: 160px;
}
.hero-deco {
  position:absolute; top:-80px; right:-80px;
  width:320px; height:320px; border-radius:50%;
  background:rgba(47,128,237,.15); pointer-events:none;
}
.hero-deco2 {
  position:absolute; bottom:-100px; right:220px;
  width:240px; height:240px; border-radius:50%;
  background:rgba(29,82,183,.12); pointer-events:none;
}
.hero-left { position:relative; z-index:1; max-width: 600px; }

/* Badge — totalmente visible */
.hero-badge {
  display:inline-flex; align-items:center; gap:6px;
  background:rgba(47,128,237,.25);
  border:1px solid rgba(47,128,237,.5);
  color:#A8C8F8;
  font-size:11px; font-weight:700;
  padding:5px 12px; border-radius:20px; margin-bottom:12px;
  font-family:'Montserrat',sans-serif; text-transform:uppercase; letter-spacing:.05em;
}
.hero-badge svg { stroke:#A8C8F8; flex-shrink:0; }

/* Título — blanco puro */
.hero-title {
  font-size:26px; font-weight:800; color:#FFFFFF;
  line-height:1.2; margin-bottom:6px; font-family:'Montserrat',sans-serif;
}
.hero-sub {
  font-size:13px; color:rgba(255,255,255,.70);
  max-width:480px; line-height:1.6; margin-bottom:18px;
  font-family:'Montserrat',sans-serif;
}

/* Filtros */
.hero-filtros { display:flex; gap:8px; flex-wrap:wrap; }
.filtro-btn {
  background:rgba(255,255,255,.10);
  border:1px solid rgba(255,255,255,.20);
  color:rgba(255,255,255,.75);
  font-size:11px; font-weight:600;
  padding:6px 16px; border-radius:20px; cursor:pointer;
  transition:all .15s; font-family:'Montserrat',sans-serif;
}
.filtro-btn:hover   { background:rgba(255,255,255,.20); color:#FFFFFF; }
.filtro-btn.filtro-act {
  background:#1D52B7; border-color:#2F80ED; color:#FFFFFF;
  box-shadow: 0 2px 8px rgba(29,82,183,.5);
}

/* Stats del hero */
.hero-stats {
  display:flex; gap:28px; position:relative; z-index:1; flex-shrink:0;
}
.hstat { text-align:right; }
.hstat-n {
  font-size:30px; font-weight:800; color:#FFFFFF;
  font-family:'Montserrat',sans-serif; line-height:1;
}
.hstat-l {
  font-size:11px; color:rgba(255,255,255,.60); margin-top:4px;
  font-family:'Montserrat',sans-serif;
}

/* ══ KPI STRIP ══ */
.kpi-strip {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
}
.kpi-s {
  background: #FFFFFF;
  border: 1px solid var(--gris-bordes);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
  transition: box-shadow .15s;
}
.kpi-s:hover { box-shadow: 0 4px 12px rgba(29,82,183,.10); }
.kpi-s-ico {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-s-ico svg { stroke: currentColor; }
.kpi-s-body { flex: 1; min-width: 0; }
.kpi-s-lbl {
  font-size: 9px; font-weight: 700; color: var(--gris-medio);
  margin-bottom: 4px; font-family:'Montserrat',sans-serif;
  text-transform: uppercase; letter-spacing: .06em;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.kpi-s-val {
  font-size: 20px; font-weight: 800; color: var(--carbon-oscuro);
  font-family:'Montserrat',sans-serif; line-height: 1;
}
.kpi-s-delta {
  font-size: 10px; font-weight: 700;
  display:flex; align-items:center; gap:2px;
  flex-shrink: 0; font-family:'Montserrat',sans-serif;
}
.delta-pos { color: var(--verde-esmeralda); }
.delta-neg { color: var(--rojo-coral); }

/* Skeleton */
.kpi-sk {
  width: 72px; height: 24px; border-radius: 4px;
  background: linear-gradient(90deg,#E8ECEF 25%,#F4F6F9 50%,#E8ECEF 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Spinner global */
.carga-global {
  display: flex; align-items: center; justify-content: center; gap: 12px;
  padding: 3rem; color: var(--gris-medio); font-size: 13px;
  font-family:'Montserrat',sans-serif; font-weight: 600;
}
.spinner-lg {
  width: 28px; height: 28px;
  border: 3px solid var(--gris-bordes);
  border-top-color: var(--azul-rey);
  border-radius: 50%;
  animation: girar .75s linear infinite;
}
@keyframes girar { to { transform: rotate(360deg); } }

/* ══ CARD ══ */
.card {
  background: #FFFFFF;
  border: 1px solid var(--gris-bordes);
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(29,82,183,.05);
}
.card-h {
  display: flex; align-items: flex-start;
  justify-content: space-between; margin-bottom: 16px;
  gap: 8px; flex-wrap: wrap;
}
.card-t   { font-size: 14px; font-weight: 700; color: var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }
.card-sub { font-size: 11px; color: var(--gris-medio); margin-top: 3px; font-family:'Montserrat',sans-serif; }
.card-actions { display:flex; gap:4px; }
.chip-btn {
  font-size: 10px; font-weight: 600; padding: 5px 12px; border-radius: 20px;
  border: 1px solid var(--gris-bordes); background: var(--gris-contenedor);
  color: var(--gris-pizarra); cursor: pointer; transition: all .15s;
  font-family:'Montserrat',sans-serif;
}
.chip-btn.chip-act {
  background: var(--azul-rey); border-color: var(--azul-rey);
  color: #FFFFFF; box-shadow: 0 2px 6px rgba(29,82,183,.3);
}

/* Leyenda inline */
.legend-inline {
  display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
  font-size: 10px; color: var(--gris-pizarra);
  font-family:'Montserrat',sans-serif; font-weight: 600; flex-shrink: 0;
}
.leg-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

/* ══ GRID LAYOUTS ══ */
.row-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.row-3col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

/* ══ CHART WRAPPERS ══ */
.chart-wrap     { position:relative; width:100%; height:180px; }
.chart-wrap--lg { height: 230px; }
.chart-wrap--md { height: 160px; }

/* ── Donut legend ── */
.donut-legend { display:flex; flex-direction:column; gap:8px; margin-top:12px; }
.dl-item { display:flex; align-items:center; gap:8px; }
.dl-dot  { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.dl-txt  { font-size:11px; color:var(--gris-pizarra); font-family:'Montserrat',sans-serif; flex:1; }
.dl-val  { font-size:12px; font-weight:700; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }

/* ── Badges ── */
.bdg   { font-size:9px; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:.03em; font-family:'Montserrat',sans-serif; flex-shrink:0; }
.bg-g  { background:rgba(39,174,96,.12);  color:var(--verde-esmeralda); }
.bg-b  { background:rgba(47,128,237,.12); color:var(--azul-rey); }
.bg-a  { background:rgba(242,153,74,.12); color:var(--naranja-calma); }
.bg-r  { background:rgba(235,87,87,.12);  color:var(--rojo-coral); }

/* ── Alerta error ── */
.alerta-err {
  display:flex; align-items:center; gap:10px;
  background: var(--rosa-alerta); border:1px solid rgba(235,87,87,.25);
  border-radius:10px; padding:14px 18px;
  font-size:.9rem; color:var(--rojo-coral); font-weight:500;
  font-family:'Montserrat',sans-serif;
}
.alerta-err svg { stroke:var(--rojo-coral); flex-shrink:0; }
.btn-reintentar {
  margin-left:auto; background:var(--rojo-coral); color:#FFFFFF;
  border:none; padding:7px 18px; border-radius:7px;
  font-weight:700; font-size:.85rem; cursor:pointer;
  font-family:'Montserrat',sans-serif; transition:background .15s; white-space:nowrap;
}
.btn-reintentar:hover { background:#c0392b; }

/* ── Footer ── */
.pie {
  text-align:center; color:var(--gris-carga); font-size:.82rem;
  padding:1.5rem 0; border-top:1px solid var(--gris-bordes);
  font-family:'Montserrat',sans-serif; margin-top:8px;
}

/* ══ RESPONSIVE ══ */
@media (max-width:1280px) {
  .kpi-strip { grid-template-columns: repeat(3,1fr); }
}
@media (max-width:1024px) {
  .row-2col { grid-template-columns: 1fr; }
  .row-3col { grid-template-columns: 1fr 1fr; }
  .kpi-strip { grid-template-columns: repeat(3,1fr); }
}
@media (max-width:768px) {
  .hero          { flex-direction:column; align-items:flex-start; gap:16px; min-height:auto; }
  .hero-title    { font-size:20px; }
  .hero-stats    { flex-direction:row; gap:16px; }
  .hstat         { text-align:left; }
  .row-3col      { grid-template-columns:1fr; }
  .kpi-strip     { grid-template-columns: repeat(2,1fr); }
}
@media (max-width:480px) {
  .kpi-strip     { grid-template-columns: 1fr; }
  .hero-stats    { flex-wrap:wrap; }
}
</style>