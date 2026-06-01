<template>
  <MainLayout>
    <div class="analytics-page">

      <div class="barra-carga" :class="{ activa: cargando }" aria-hidden="true">
        <div class="barra-progreso"></div>
      </div>

      <div class="analytics-header">
        <div class="header-left">
          <div class="header-breadcrumb">
            <router-link to="/" class="bc-link">INICIO</router-link>
            <ChevronRight :size="14" class="bc-sep" />
            <span class="bc-actual">ANALISIS ACADEMICO</span>
          </div>
          <h1 class="page-title">
            <TrendingUp :size="28" class="title-icon" />
            ANALISIS ACADEMICO
          </h1>
          <p class="page-subtitle">RENDIMIENTO Y ESTADISTICAS POR PERIODO</p>
        </div>
        <div class="header-right">
          <div class="periodo-badge">
            <Calendar :size="14" />
            <span>{{ periodoActual }}</span>
          </div>
          <button class="btn-exportar" @click="exportarReporte" :disabled="cargando">
            <Download :size="15" />
            EXPORTAR
          </button>
          <button class="btn-actualizar" @click="cargarDatos" :disabled="cargando">
            <RefreshCw :size="15" :class="{ 'girando': cargando }" />
            ACTUALIZAR
          </button>
        </div>
      </div>

      <Transition name="fade">
        <div v-if="error" class="alerta-error" role="alert">
          <AlertCircle :size="18" />
          {{ error }}
        </div>
      </Transition>

      <div class="filtros-panel">
        <div class="filtros-titulo">
          <SlidersHorizontal :size="16" />
          <span>FILTROS</span>
        </div>
        <div class="filtros-controles">
          <div class="filtro-grupo">
            <label class="filtro-label">CARRERA</label>
            <select v-model="filtroCarrera" class="filtro-select" @change="aplicarFiltros">
              <option value="">TODAS LAS CARRERAS</option>
              <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
            </select>
          </div>
          <div class="filtro-grupo">
            <label class="filtro-label">SEMESTRE</label>
            <select v-model="filtroSemestre" class="filtro-select" @change="aplicarFiltros">
              <option value="">TODOS</option>
              <option v-for="n in 8" :key="n" :value="n">{{ n }}° SEMESTRE</option>
            </select>
          </div>
          <div class="filtro-grupo">
            <label class="filtro-label">PERIODO</label>
            <select v-model="filtroPeriodo" class="filtro-select" @change="aplicarFiltros">
              <option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
            </select>
          </div>
          <button v-if="hayFiltrosActivos" class="btn-limpiar-filtros" @click="limpiarFiltros">
            <X :size="13" />
            LIMPIAR
          </button>
        </div>
      </div>

      <div class="kpi-grid">
        <div
          v-for="kpi in kpis"
          :key="kpi.id"
          class="kpi-card"
          :class="kpi.clase"
        >
          <div class="kpi-icono-wrap">
            <component :is="kpi.icono" :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-valor">
              <span v-if="cargando" class="kpi-skeleton"></span>
              <template v-else>
                {{ kpi.valor }}<span v-if="kpi.sufijo" class="kpi-sufijo">{{ kpi.sufijo }}</span>
              </template>
            </div>
            <div class="kpi-etiqueta">{{ kpi.etiqueta }}</div>
          </div>
          <div class="kpi-tendencia" :class="kpi.tendenciaClase" v-if="!cargando">
            <component :is="kpi.tendenciaIcono" :size="13" />
            <span>{{ kpi.tendencia }}</span>
          </div>
        </div>
      </div>

      <div class="graficas-grid integracion-chartjs" style="margin-bottom: 1rem;">
        <div class="grafica-card">
          <div class="grafica-header">
            <div class="grafica-titulo-wrap">
              <BarChart3 :size="18" class="grafica-icono" />
              <h2 class="grafica-titulo">RENDIMIENTO POR CARRERA</h2>
            </div>
          </div>
          <div class="chart-wrap" style="position: relative; height: 250px; width: 100%;">
            <Bar v-if="!cargando" :data="dataRendimiento" :options="chartOptions" />
            <div v-else class="tendencia-skeleton" style="height: 100%;"></div>
          </div>
        </div>

        <div class="grafica-card">
          <div class="grafica-header">
            <div class="grafica-titulo-wrap">
              <BarChart3 :size="18" class="grafica-icono" />
              <h2 class="grafica-titulo">COMPARATIVO DE GRUPOS (PROMEDIO)</h2>
            </div>
          </div>
          <div class="chart-wrap" style="position: relative; height: 250px; width: 100%;">
            <Bar v-if="!cargando" :data="dataComparativoGrupos" :options="chartOptions" />
            <div v-else class="tendencia-skeleton" style="height: 100%;"></div>
          </div>
        </div>
      </div>

      <div class="graficas-grid">

        <div class="grafica-card grafica-parciales">
          <div class="grafica-header">
            <div class="grafica-titulo-wrap">
              <BarChart3 :size="18" class="grafica-icono" />
              <h2 class="grafica-titulo">APROBADOS / REPROBADOS POR UNIDAD</h2>
            </div>
            <div class="grafica-leyenda">
              <span class="leyenda-item aprobado"><span class="leyenda-dot"></span>APROBADOS</span>
              <span class="leyenda-item reprobado"><span class="leyenda-dot"></span>REPROBADOS</span>
            </div>
          </div>

          <div v-if="cargando" class="grafica-skeleton">
            <div v-for="i in 4" :key="i" class="skeleton-bar-wrap">
              <div class="skeleton-bar" :style="{ height: `${40 + i * 15}%` }"></div>
              <div class="skeleton-bar" :style="{ height: `${20 + i * 8}%` }"></div>
            </div>
          </div>

          <div v-else class="barras-parciales">
            <div
              v-for="(unidad, idx) in datosUnidades"
              :key="idx"
              class="parcial-grupo"
            >
              <div class="barras-wrap">
                <div class="barra-col">
                  <span class="barra-valor-top text-azul">{{ unidad.aprobados }}</span>
                  <div
                    class="barra-rect aprobado-bar"
                    :style="{ height: calcularAlturaBarra(unidad.aprobados) }"
                    :title="`${unidad.aprobados} alumnos aprobados`"
                  ></div>
                </div>
                <div class="barra-col">
                  <span class="barra-valor-top text-rojo">{{ unidad.reprobados }}</span>
                  <div
                    class="barra-rect reprobado-bar"
                    :style="{ height: calcularAlturaBarra(unidad.reprobados) }"
                    :title="`${unidad.reprobados} alumnos reprobados`"
                  ></div>
                </div>
              </div>
              <div class="parcial-etiqueta">{{ unidad.etiqueta }}</div>
              <div class="parcial-pct">
                <span class="pct-aprobado">{{ unidad.pctAprobado }}%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="grafica-card grafica-tronco">
          <div class="grafica-header">
            <div class="grafica-titulo-wrap">
              <BookOpen :size="18" class="grafica-icono" />
              <h2 class="grafica-titulo">RENDIMIENTO TRONCO COMUN</h2>
            </div>
            <span class="grafica-badge">POR CARRERA</span>
          </div>

          <div v-if="cargando" class="tronco-skeleton">
            <div v-for="i in 5" :key="i" class="skeleton-row">
              <div class="skeleton-label"></div>
              <div class="skeleton-track"><div class="skeleton-fill" :style="{ width: `${30 + i * 12}%` }"></div></div>
            </div>
          </div>

          <div v-else class="tronco-lista">
            <div
              v-for="(materia, idx) in datosTronco"
              :key="idx"
              class="tronco-item"
              :class="{ 'riesgo-alto': materia.promedio < 6.0, 'riesgo-medio': materia.promedio >= 6.0 && materia.promedio < 7.0 }"
            >
              <div class="tronco-info">
                <span class="tronco-nombre">{{ materia.nombre }}</span>
                <div class="tronco-stats">
                  <span class="tronco-promedio" :class="clasePromedio(materia.promedio)">
                    {{ materia.promedio.toFixed(1) }}
                  </span>
                  <span class="tronco-alumnos">{{ materia.alumnos }} ALUM.</span>
                </div>
              </div>
              <div class="tronco-track">
                <div
                  class="tronco-fill"
                  :class="claseFill(materia.promedio)"
                  :style="{ width: (materia.promedio / 10 * 100) + '%' }"
                ></div>
              </div>
              <div class="tronco-pct-reprobados">
                <span>{{ materia.pctReprobados }}%</span>
                <span class="tronco-label-rep">REP</span>
              </div>
            </div>
          </div>
        </div>

      </div>
      
      <div class="fila-inferior">

        <div class="panel-card panel-estatus">
          <div class="panel-header">
            <PieChart :size="17" class="panel-icono" />
            <h3 class="panel-titulo">DISTRIBUCION POR ESTATUS</h3>
          </div>

          <div v-if="cargando" class="estatus-skeleton">
            <div class="donut-skeleton"></div>
          </div>

          <div v-else class="estatus-contenido">
            <div class="donut-wrap">
              <svg viewBox="0 0 120 120" class="donut-svg" aria-hidden="true">
                <circle cx="60" cy="60" r="48" fill="none" stroke="#E5E7EB" stroke-width="14"></circle>
                <circle
                  cx="60" cy="60" r="48" fill="none"
                  stroke="#16A34A" stroke-width="14"
                  stroke-dasharray="301.59"
                  :stroke-dashoffset="301.59 * (1 - datosEstatus.regularPct / 100)"
                  stroke-linecap="round"
                  transform="rotate(-90 60 60)"
                  style="transition: stroke-dashoffset 1s ease"
                ></circle>
                <circle
                  cx="60" cy="60" r="48" fill="none"
                  stroke="#F59E0B" stroke-width="14"
                  stroke-dasharray="301.59"
                  :stroke-dashoffset="301.59 * (1 - datosEstatus.condicionadoPct / 100)"
                  stroke-linecap="round"
                  transform="rotate(-90 60 60)"
                  style="transition: stroke-dashoffset 1s ease; opacity: 0.4"
                ></circle>
              </svg>
              <div class="donut-centro">
                <span class="donut-valor">{{ datosEstatus.regularPct }}%</span>
                <span class="donut-label">REGULARES</span>
              </div>
            </div>

            <div class="estatus-lista">
              <div class="estatus-item">
                <span class="estatus-dot" style="background:#16A34A"></span>
                <span class="estatus-nombre">REGULARES</span>
                <span class="estatus-num">{{ datosEstatus.regulares }}</span>
              </div>
              <div class="estatus-item">
                <span class="estatus-dot" style="background:#F59E0B"></span>
                <span class="estatus-nombre">CONDICIONADOS</span>
                <span class="estatus-num">{{ datosEstatus.condicionados }}</span>
              </div>
              <div class="estatus-item">
                <span class="estatus-dot" style="background:#DC2626"></span>
                <span class="estatus-nombre">IRREGULARES</span>
                <span class="estatus-num">{{ datosEstatus.irregulares }}</span>
              </div>
              <div class="estatus-item estatus-total">
                <span class="estatus-nombre">TOTAL</span>
                <span class="estatus-num">{{ datosEstatus.total }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="panel-card panel-riesgo">
          <div class="panel-header">
            <AlertTriangle :size="17" class="panel-icono riesgo-icono" />
            <h3 class="panel-titulo">MATERIAS A PREVENIR</h3>
            <span class="badge-alerta">{{ materiasRiesgo.length }} ALERTAS</span>
          </div>

          <div v-if="cargando" class="riesgo-skeleton">
            <div v-for="i in 4" :key="i" class="skeleton-riesgo-item"></div>
          </div>

          <div v-else-if="materiasRiesgo.length === 0" class="estado-vacio">
            <CheckCircle2 :size="32" class="vacio-icono" />
            <p>SIN ALERTAS ACTIVAS</p>
          </div>

          <div v-else class="riesgo-lista">
            <div
              v-for="(materia, idx) in materiasRiesgo"
              :key="idx"
              class="riesgo-item"
              :class="nivelRiesgo(materia.pctReprobados)"
            >
              <div class="riesgo-rank">{{ idx + 1 }}</div>
              <div class="riesgo-info">
                <p class="riesgo-nombre">{{ materia.nombre }}</p>
                <p class="riesgo-carrera">{{ materia.carrera }} · {{ materia.semestre }}° SEM</p>
              </div>
              <div class="riesgo-metricas">
                <div class="riesgo-pct">{{ materia.pctReprobados }}%</div>
                <div class="riesgo-sublabel">REPROBADOS</div>
              </div>
              <div class="riesgo-barra-wrap">
                <div
                  class="riesgo-barra"
                  :style="{ width: materia.pctReprobados + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel-card panel-tendencia">
          <div class="panel-header">
            <LineChart :size="17" class="panel-icono" />
            <h3 class="panel-titulo">TENDENCIA SEMESTRAL</h3>
          </div>

          <div v-if="cargando" class="tendencia-skeleton"></div>

          <div v-else class="tendencia-contenido">
            <svg viewBox="0 0 300 140" class="linea-svg" aria-hidden="true">
              <line v-for="i in 5" :key="i"
                :x1="0" :y1="i * 28 - 14" :x2="300" :y2="i * 28 - 14"
                stroke="#E5E7EB" stroke-width="1"
              ></line>
              <path
                :d="areaPath"
                fill="url(#grad-tendencia)"
                opacity="0.25"
              ></path>
              <path
                :d="linePath"
                fill="none"
                stroke="#1B396A"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              ></path>
              <circle
                v-for="(punto, i) in puntosTendencia"
                :key="i"
                :cx="punto.x"
                :cy="punto.y"
                r="4"
                fill="#1B396A"
                stroke="white"
                stroke-width="2"
              ></circle>
              <defs>
                <linearGradient id="grad-tendencia" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#1B396A"></stop>
                  <stop offset="100%" stop-color="white"></stop>
                </linearGradient>
              </defs>
            </svg>

            <div class="tendencia-etiquetas">
              <span v-for="(punto, i) in puntosTendencia" :key="i" class="tend-label">
                {{ punto.etiqueta }}
              </span>
            </div>

            <div class="tendencia-resumen">
              <div class="tend-stat">
                <span class="tend-stat-val verde">{{ mejorSemestre.promedio }}</span>
                <span class="tend-stat-lab">MEJOR SEMESTRE</span>
              </div>
              <div class="tend-stat">
                <span class="tend-stat-val azul">{{ promedioGeneral }}</span>
                <span class="tend-stat-lab">PROMEDIO GRAL</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import {
  TrendingUp, Calendar, Download, RefreshCw, AlertCircle,
  SlidersHorizontal, X, BarChart3, BookOpen, PieChart,
  AlertTriangle, CheckCircle2, LineChart, ChevronRight,
  Users, GraduationCap, Award, TrendingDown, UserMinus
} from 'lucide-vue-next'

// Importaciones de Chart.js para los módulos integrados
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const API_URL = import.meta.env.VITE_API_URL

// ── Opciones y Datos para Chart.js (Nuevas gráficas) ───────────────
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { font: { family: 'Montserrat', size: 11 }, color: '#828282' } } }
}

const dataRendimiento = ref({
  labels: ['SISTEMAS', 'INDUSTRIAL', 'ELÉCTRICA', 'MECÁNICA', 'GESTIÓN', 'BIOQUÍMICA'],
  datasets: [{ label: 'PROMEDIO GENERAL', backgroundColor: '#1D52B7', borderRadius: 6, data: [8.5, 8.2, 8.8, 7.9, 8.4, 8.1] }]
})

const dataComparativoGrupos = ref({
  labels: ['1A', '1B', '2A', '3A', '3B', '5A'],
  datasets: [{ label: 'PROMEDIO DEL GRUPO', backgroundColor: '#0B2545', borderRadius: 6, data: [8.9, 7.5, 8.2, 9.1, 8.0, 8.6] }]
})

// ── Estado general ─────────────────────────────────────────────────
const cargando = ref(true)
const error    = ref(null)

// ── Filtros ────────────────────────────────────────────────────────
const filtroCarrera  = ref('')
const filtroSemestre = ref('')
const filtroPeriodo  = ref('2025-A')

const periodoActual = ref('ENERO - JUNIO 2025')
const periodos = ref([
  { id: '2025-A', nombre: 'ENERO - JUNIO 2025' },
  { id: '2024-B', nombre: 'AGOSTO - DICIEMBRE 2024' },
  { id: '2024-A', nombre: 'ENERO - JUNIO 2024' }
])

const carreras = ref([
  { id: 1, nombre: 'SISTEMAS COMPUTACIONALES' },
  { id: 2, nombre: 'INDUSTRIAL'               },
  { id: 3, nombre: 'ELECTRICA'                },
  { id: 4, nombre: 'MECANICA'                 },
  { id: 5, nombre: 'GESTION EMPRESARIAL'      },
  { id: 6, nombre: 'BIOQUIMICA'               }
])

const hayFiltrosActivos = computed(() =>
  filtroCarrera.value !== '' || filtroSemestre.value !== ''
)

const aplicarFiltros = () => cargarDatos()
const limpiarFiltros = () => {
  filtroCarrera.value  = ''
  filtroSemestre.value = ''
  cargarDatos()
}

// ── KPI Cards (Incluye el KPI de Deserción) ──────────────────
const kpisData = ref({
  totalAlumnos:      1284,
  promedioGeneral:   7.42,
  pctAprobacion:     74.6,
  alumnosRiesgo:     187,
  tasaDesercion:     12.4
})

const kpis = computed(() => [
  {
    id: 'total',
    icono: Users,
    valor: kpisData.value.totalAlumnos.toLocaleString(),
    etiqueta: 'ALUMNOS ACTIVOS',
    clase: 'kpi-azul',
    tendencia: '+3.2% VS PERIODO ANT.',
    tendenciaClase: 'tend-positiva',
    tendenciaIcono: TrendingUp,
    sufijo: ''
  },
  {
    id: 'promedio',
    icono: GraduationCap,
    valor: kpisData.value.promedioGeneral.toFixed(2),
    etiqueta: 'PROMEDIO GENERAL',
    clase: 'kpi-verde',
    tendencia: '+0.18 VS PERIODO ANT.',
    tendenciaClase: 'tend-positiva',
    tendenciaIcono: TrendingUp,
    sufijo: ''
  },
  {
    id: 'aprobacion',
    icono: Award,
    valor: kpisData.value.pctAprobacion,
    etiqueta: 'PORCENTAJE APROBACION',
    clase: 'kpi-esmeralda',
    tendencia: '+2.1% VS PERIODO ANT.',
    tendenciaClase: 'tend-positiva',
    tendenciaIcono: TrendingUp,
    sufijo: '%'
  },
  {
    id: 'riesgo',
    icono: AlertTriangle,
    valor: kpisData.value.alumnosRiesgo,
    etiqueta: 'ALUMNOS EN RIESGO',
    clase: 'kpi-rojo',
    tendencia: '-12 VS PERIODO ANT.',
    tendenciaClase: 'tend-positiva',
    tendenciaIcono: TrendingDown,
    sufijo: ''
  },
  {
    id: 'desercion',
    icono: UserMinus,
    valor: kpisData.value.tasaDesercion,
    etiqueta: 'TASA DE DESERCIÓN',
    clase: 'kpi-amarillo',
    tendencia: '-1.2% VS PERIODO ANT.',
    tendenciaClase: 'tend-positiva', 
    tendenciaIcono: TrendingDown,
    sufijo: '%'
  }
])

// ── Datos gráfica unidades ────────────────────────────────────────
const datosUnidades = ref([
  { etiqueta: '1° UNIDAD',   aprobados: 312, reprobados: 88,  pctAprobado: 78 },
  { etiqueta: '2° UNIDAD',   aprobados: 298, reprobados: 102, pctAprobado: 74 },
  { etiqueta: '3° UNIDAD',   aprobados: 276, reprobados: 124, pctAprobado: 69 },
  { etiqueta: 'FINAL',       aprobados: 289, reprobados: 111, pctAprobado: 72 }
])

const maxBarra = computed(() =>
  Math.max(...datosUnidades.value.flatMap(p => [p.aprobados, p.reprobados]))
)

const calcularAlturaBarra = (val) => {
  const pct = (val / maxBarra.value) * 100
  return `${Math.max(pct, 4)}%`
}

// ── Datos tronco común ─────────────────────────────────────────────
const datosTronco = ref([
  { nombre: 'CALCULO DIFERENCIAL',     promedio: 5.8,  alumnos: 420, pctReprobados: 42 },
  { nombre: 'ALGEBRA LINEAL',          promedio: 6.2,  alumnos: 380, pctReprobados: 35 },
  { nombre: 'FISICA GENERAL',          promedio: 6.5,  alumnos: 410, pctReprobados: 28 },
  { nombre: 'QUIMICA BASICA',          promedio: 7.1,  alumnos: 310, pctReprobados: 20 },
  { nombre: 'COMUNICACION ORAL',       promedio: 8.2,  alumnos: 450, pctReprobados:  8 },
  { nombre: 'PROGRAMACION BASICA',     promedio: 7.4,  alumnos: 320, pctReprobados: 18 },
  { nombre: 'ECUACIONES DIFERENCIALES',promedio: 5.5,  alumnos: 280, pctReprobados: 48 }
])

const clasePromedio = (prom) => {
  if (prom < 6.0) return 'prom-rojo'
  if (prom < 7.0) return 'prom-amarillo'
  return 'prom-verde'
}

const claseFill = (prom) => {
  if (prom < 6.0) return 'fill-rojo'
  if (prom < 7.0) return 'fill-amarillo'
  return 'fill-verde'
}

// ── Datos estatus ──────────────────────────────────────────────────
const datosEstatus = ref({
  regulares:       896,
  condicionados:   201,
  irregulares:     187,
  total:           1284,
  regularPct:      70,
  condicionadoPct: 16
})

// ── Materias en riesgo ─────────────────────────────────────────────
const materiasRiesgo = ref([
  { nombre: 'ECUACIONES DIFERENCIALES', carrera: 'SISTEMAS',   semestre: 3, pctReprobados: 48 },
  { nombre: 'CALCULO DIFERENCIAL',      carrera: 'TODAS',      semestre: 1, pctReprobados: 42 },
  { nombre: 'ALGEBRA LINEAL',           carrera: 'INDUSTRIAL', semestre: 2, pctReprobados: 35 },
  { nombre: 'FISICA GENERAL',           carrera: 'ELECTRICA',  semestre: 2, pctReprobados: 28 },
  { nombre: 'RESISTENCIA MATERIALES',   carrera: 'MECANICA',   semestre: 4, pctReprobados: 24 }
])

const nivelRiesgo = (pct) => {
  if (pct >= 40) return 'riesgo-critico'
  if (pct >= 25) return 'riesgo-alto'
  return 'riesgo-medio'
}

// ── Tendencia semestral ────────────────────────────────────────────
const datosTendencia = ref([
  { semestre: '2024-A', promedio: 7.08, etiqueta: 'ENE-JUN 24' },
  { semestre: '2024-B', promedio: 7.19, etiqueta: 'AGO-DIC 24' },
  { semestre: '2025-A', promedio: 7.42, etiqueta: 'ENE-JUN 25' }
])

const puntosTendencia = computed(() => {
  const n    = datosTendencia.value.length
  const minP = 6.5
  const maxP = 8.0
  return datosTendencia.value.map((d, i) => ({
    x:        30 + (i / (n - 1)) * 240,
    y:        120 - ((d.promedio - minP) / (maxP - minP)) * 100,
    etiqueta: d.etiqueta,
    promedio: d.promedio
  }))
})

const linePath = computed(() => {
  const pts = puntosTendencia.value
  if (!pts.length) return ''
  return pts.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ')
})

const areaPath = computed(() => {
  const pts = puntosTendencia.value
  if (!pts.length) return ''
  const line = pts.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ')
  const last = pts[pts.length - 1]
  const first = pts[0]
  return `${line} L${last.x},130 L${first.x},130 Z`
})

const mejorSemestre = computed(() => {
  return datosTendencia.value.reduce((best, curr) =>
    curr.promedio > best.promedio ? curr : best, datosTendencia.value[0])
})

const promedioGeneral = computed(() => {
  const sum = datosTendencia.value.reduce((s, d) => s + d.promedio, 0)
  return (sum / datosTendencia.value.length).toFixed(2)
})

// ── Carga de datos ─────────────────────────────────────────────────
const cargarDatos = async () => {
  cargando.value = true
  error.value    = null
  try {
    const params = new URLSearchParams()
    if (filtroCarrera.value)  params.append('carrera',  filtroCarrera.value)
    if (filtroSemestre.value) params.append('semestre', filtroSemestre.value)
    if (filtroPeriodo.value)  params.append('periodo',  filtroPeriodo.value)

    const res = await fetch(`${API_URL}/api/analytics/academico?${params}`)
    if (!res.ok) throw new Error()
    const data = await res.json()

    if (data.kpis)             kpisData.value    = data.kpis
    if (data.parciales)        datosUnidades.value = data.parciales
    if (data.tronco)           datosTronco.value = data.tronco
    if (data.estatus)          datosEstatus.value = data.estatus
    if (data.materias_riesgo)  materiasRiesgo.value = data.materias_riesgo
    if (data.tendencia)        datosTendencia.value = data.tendencia
  } catch {
    // Backend no disponible
  } finally {
    setTimeout(() => { cargando.value = false }, 500) // Simulación ligera
  }
}

const exportarReporte = () => {
  console.log('[Analytics] Exportar reporte')
}

onMounted(() => {
  cargarDatos()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   VARIABLES  —  paleta SICE (consistente con Dashboard)
══════════════════════════════════════════════════════ */
.analytics-page {
  --azul:         #1B396A;
  --azul-hover:   #15305A;
  --azul-claro:   #DBEAFE;
  --verde:        #16A34A;
  --verde-claro:  #DCFCE7;
  --rojo:         #DC2626;
  --rojo-claro:   #FEF2F2;
  --amarillo:     #F59E0B;
  --amarillo-claro: #FEF3C7;
  --borde:        #E5E7EB;
  --fondo:        #F9FAFB;
  --texto:        #111827;
  --gris:         #6B7280;
  
  --radio:        16px; 
  --sombra:       0 4px 12px rgba(11, 37, 69, 0.05);

  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2.5rem;
}

/* ── Barra de carga ── */
.barra-carga { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999; opacity: 0; transition: opacity .2s; pointer-events: none; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: var(--azul); animation: progreso-carga 1.5s ease-in-out infinite; }
@keyframes progreso-carga { 0%{width:0%;opacity:1} 70%{width:85%;opacity:1} 100%{width:100%;opacity:0} }

/* ── Encabezado ── */
.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.4rem;
  flex-wrap: wrap;
  gap: 1rem;
}
.header-breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  margin-bottom: 6px;
  color: var(--gris);
}
.bc-link   { color: var(--gris); text-decoration: none; transition: color .15s; }
.bc-link:hover { color: var(--azul); }
.bc-sep    { stroke: var(--gris); }
.bc-actual { color: var(--azul); font-weight: 600; text-transform: uppercase; }
.page-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--texto);
  margin: 0 0 4px;
}
.title-icon { stroke: var(--azul); }
.page-subtitle { font-size: 0.82rem; color: var(--gris); margin: 0; font-weight: 500; letter-spacing: .05em; text-transform: uppercase;}

.header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.periodo-badge {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.8rem; font-weight: 600;
  color: var(--gris);
  background: var(--fondo);
  border: 1px solid var(--borde);
  border-radius: 20px;
  padding: 6px 14px;
}
.periodo-badge svg { stroke: var(--gris); }

.btn-exportar, .btn-actualizar {
  display: flex; align-items: center; gap: 7px;
  padding: 9px 16px; border-radius: 8px;
  font-size: 0.8rem; font-weight: 700;
  cursor: pointer; font-family: inherit;
  transition: all .15s;
}
.btn-exportar {
  background: var(--fondo); color: var(--texto);
  border: 1px solid var(--borde);
}
.btn-exportar:hover { background: var(--borde); }
.btn-exportar svg { stroke: var(--gris); }

.btn-actualizar {
  background: var(--azul); color: white;
  border: none;
}
.btn-actualizar:hover:not(:disabled) { background: var(--azul-hover); transform: translateY(-1px); }
.btn-actualizar:disabled { opacity: .6; cursor: not-allowed; }
.btn-actualizar svg { stroke: white; }
.girando { animation: girar 1s linear infinite; }
@keyframes girar { to { transform: rotate(360deg); } }

/* ── Alerta de error ── */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: var(--rojo-claro); border: 1px solid #FECACA;
  border-radius: var(--radio); padding: 12px 16px;
  margin-bottom: 1.2rem;
  font-size: .875rem; font-weight: 500; color: var(--rojo);
}
.alerta-error svg { stroke: var(--rojo); flex-shrink: 0; }

/* ── Filtros ── */
.filtros-panel {
  background: white;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
  padding: .9rem 1.2rem;
  margin-bottom: 1.4rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  box-shadow: var(--sombra);
}
.filtros-titulo {
  display: flex; align-items: center; gap: 6px;
  font-size: .8rem; font-weight: 700;
  color: var(--gris); white-space: nowrap;
}
.filtros-titulo svg { stroke: var(--gris); }
.filtros-controles {
  display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; flex: 1;
}
.filtro-grupo { display: flex; flex-direction: column; gap: 3px; }
.filtro-label { font-size: .7rem; font-weight: 700; color: var(--gris); letter-spacing: .04em; }
.filtro-select {
  padding: 7px 10px; border: 1px solid var(--borde);
  border-radius: 8px; font-size: .82rem; font-family: inherit;
  color: var(--texto); background: var(--fondo); outline: none;
  cursor: pointer; transition: border-color .15s; text-transform: uppercase;
}
.filtro-select:focus { border-color: var(--azul); }

.btn-limpiar-filtros {
  display: flex; align-items: center; gap: 5px;
  padding: 7px 14px;
  background: var(--rojo-claro); color: var(--rojo);
  border: 1px solid #FECACA; border-radius: 8px;
  font-size: .8rem; font-weight: 700; cursor: pointer;
  font-family: inherit; transition: background .15s;
  align-self: flex-end;
}
.btn-limpiar-filtros:hover { background: #FEE2E2; }
.btn-limpiar-filtros svg { stroke: var(--rojo); }

/* ── KPI Grid ── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.2rem;
}
.kpi-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
  padding: 1.2rem 1.4rem;
  display: flex;
  flex-direction: column;
  gap: 8px;
  box-shadow: var(--sombra);
  transition: transform .2s, box-shadow .2s;
  position: relative;
  overflow: hidden;
}
.kpi-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
}
.kpi-azul::before      { background: var(--azul); }
.kpi-verde::before     { background: var(--verde); }
.kpi-esmeralda::before { background: #059669; }
.kpi-rojo::before      { background: var(--rojo); }
.kpi-amarillo::before  { background: var(--amarillo); }

.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(11, 37, 69, .08); }

.kpi-icono-wrap {
  display: flex; align-items: center; justify-content: center;
  width: 40px; height: 40px; border-radius: 10px;
}
.kpi-azul .kpi-icono-wrap      { background: var(--azul-claro); }
.kpi-azul .kpi-icono-wrap svg  { stroke: var(--azul); }
.kpi-verde .kpi-icono-wrap     { background: var(--verde-claro); }
.kpi-verde .kpi-icono-wrap svg { stroke: var(--verde); }
.kpi-esmeralda .kpi-icono-wrap     { background: #D1FAE5; }
.kpi-esmeralda .kpi-icono-wrap svg { stroke: #059669; }
.kpi-rojo .kpi-icono-wrap     { background: var(--rojo-claro); }
.kpi-rojo .kpi-icono-wrap svg { stroke: var(--rojo); }
.kpi-amarillo .kpi-icono-wrap     { background: var(--amarillo-claro); }
.kpi-amarillo .kpi-icono-wrap svg { stroke: var(--amarillo); }

.kpi-info { flex: 1; }
.kpi-valor {
  font-size: 1.9rem; font-weight: 800;
  color: var(--texto); line-height: 1; margin-bottom: 4px;
}
.kpi-sufijo { font-size: 1.1rem; font-weight: 700; }
.kpi-etiqueta { font-size: .72rem; font-weight: 700; color: var(--gris); letter-spacing: .04em; }

.kpi-tendencia {
  display: flex; align-items: center; gap: 4px;
  font-size: .72rem; font-weight: 700;
}
.tend-positiva { color: var(--verde); }
.tend-positiva svg { stroke: var(--verde); }
.tend-negativa { color: var(--rojo); }
.tend-negativa svg { stroke: var(--rojo); }

.kpi-skeleton {
  display: block;
  width: 80px; height: 2rem;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
}

/* ── Gráficas grid ── */
.graficas-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
  align-items: stretch;
}
.grafica-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
  padding: 1.4rem 1.6rem;
  box-shadow: var(--sombra);
  display: flex; 
  flex-direction: column; 
}
.grafica-header {
  display: flex; align-items: center;
  justify-content: space-between;
  margin-bottom: 1.4rem; flex-wrap: wrap; gap: .5rem;
  flex-shrink: 0; 
}
.grafica-titulo-wrap { display: flex; align-items: center; gap: 8px; }
.grafica-icono { stroke: var(--azul); }
.grafica-titulo {
  font-size: .9rem; font-weight: 700;
  color: var(--texto); margin: 0;
}
.grafica-badge {
  font-size: .7rem; font-weight: 700;
  background: var(--azul-claro); color: var(--azul);
  border-radius: 20px; padding: 3px 10px;
}
.grafica-leyenda { display: flex; gap: 1rem; }
.leyenda-item {
  display: flex; align-items: center; gap: 5px;
  font-size: .74rem; font-weight: 600; color: var(--gris);
}
.leyenda-dot { width: 10px; height: 10px; border-radius: 3px; }
.aprobado .leyenda-dot { background: var(--azul); }
.reprobado .leyenda-dot { background: var(--rojo); }

/* ══════════════════════════════════════════════════════
   BARRAS: Expansión total con números APILADOS
══════════════════════════════════════════════════════ */
.barras-parciales {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  flex: 1; 
  min-height: 200px; 
  padding-bottom: 0.5rem;
  width: 100%;
  align-items: end;
}

.parcial-grupo {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  height: 100%;
  width: 100%;
}

.barras-wrap {
  display: flex;
  width: 100%; 
  max-width: 160px; 
  flex: 1; 
  margin: 0 auto; 
  gap: 0; 
  align-items: flex-end;
}

.barra-col {
  flex: 1; 
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center; 
  justify-content: flex-end;
}

.barra-valor-top {
  font-size: .7rem; 
  font-weight: 800;
  margin-bottom: 4px;
  white-space: nowrap;
}
.text-azul { color: var(--azul); }
.text-rojo { color: var(--rojo); }

.barra-rect {
  width: 100%;
  transition: height 0.6s ease;
  min-height: 4px;
}

.aprobado-bar { background: var(--azul); border-radius: 6px 0 0 0; }
.reprobado-bar { background: var(--rojo); opacity: 0.85; border-radius: 0 6px 0 0; }

.parcial-etiqueta {
  font-size: .72rem; font-weight: 700;
  color: var(--gris); margin-top: 8px;
  text-align: center;
}
.parcial-pct { font-size: .7rem; font-weight: 700; color: var(--verde); }
.pct-aprobado { color: var(--verde); }

/* Skeleton barras */
.grafica-skeleton {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  align-items: flex-end; 
  flex: 1;
  min-height: 200px;
  width: 100%;
}
.skeleton-bar-wrap { 
  display: flex; 
  gap: 0; 
  align-items: flex-end; 
  height: 100%;
  width: 100%;
  max-width: 160px;
  margin: 0 auto;
}
.skeleton-bar {
  flex: 1;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
.skeleton-bar:first-child { border-radius: 6px 0 0 0; }
.skeleton-bar:last-child { border-radius: 0 6px 0 0; }


/* Tronco común */
.tronco-lista { display: flex; flex-direction: column; gap: 10px; }
.tronco-item {
  padding: 8px 10px;
  border-radius: 8px;
  background: var(--fondo);
  border: 1px solid var(--borde);
  transition: background .15s;
}
.tronco-item:hover { background: var(--azul-claro); }
.riesgo-alto  { border-left: 3px solid var(--amarillo); }
.riesgo-alto.tronco-item:hover { background: var(--amarillo-claro); }

.tronco-info {
  display: flex; justify-content: space-between;
  align-items: center; margin-bottom: 6px;
}
.tronco-nombre {
  font-size: .8rem; font-weight: 700; color: var(--texto);
}
.tronco-stats { display: flex; align-items: center; gap: 8px; }
.tronco-promedio {
  font-size: .95rem; font-weight: 800;
}
.prom-rojo    { color: var(--rojo); }
.prom-amarillo { color: var(--amarillo); }
.prom-verde   { color: var(--verde); }
.tronco-alumnos { font-size: .7rem; color: var(--gris); font-weight: 600; }

.tronco-track {
  height: 6px; background: var(--borde);
  border-radius: 3px; overflow: hidden; margin-bottom: 4px;
}
.tronco-fill { height: 100%; border-radius: 3px; transition: width .6s ease; }
.fill-rojo    { background: var(--rojo); }
.fill-amarillo { background: var(--amarillo); }
.fill-verde   { background: var(--verde); }

.tronco-pct-reprobados {
  display: flex; align-items: center; gap: 4px;
  font-size: .7rem; font-weight: 700;
  color: var(--rojo); justify-content: flex-end;
}
.tronco-label-rep { font-weight: 600; color: var(--gris); }

.tronco-skeleton .skeleton-row {
  display: flex; align-items: center; gap: 10px; padding: 8px 0;
}
.skeleton-label { width: 140px; height: 12px; border-radius: 4px; background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-track { flex: 1; height: 8px; border-radius: 4px; background: #E5E7EB; overflow: hidden; }
.skeleton-fill  { height: 100%; background: linear-gradient(90deg, #D1D5DB 25%, #E5E7EB 50%, #D1D5DB 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }

/* ── Fila inferior ── */
.fila-inferior {
  display: grid;
  grid-template-columns: 280px 1fr 320px;
  gap: 1rem;
}
.panel-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
  padding: 1.4rem 1.6rem;
  box-shadow: var(--sombra);
}
.panel-header {
  display: flex; align-items: center; gap: 8px;
  margin-bottom: 1.2rem; flex-wrap: wrap;
}
.panel-titulo { font-size: .9rem; font-weight: 700; color: var(--texto); margin: 0; flex: 1; text-transform: uppercase;}
.panel-icono { stroke: var(--azul); }
.riesgo-icono { stroke: var(--rojo) !important; }

/* Panel estatus / donut */
.donut-wrap {
  position: relative;
  width: 120px; height: 120px;
  margin: 0 auto 1rem;
}
.donut-svg { width: 100%; height: 100%; }
.donut-centro {
  position: absolute;
  inset: 0; display: flex;
  flex-direction: column;
  align-items: center; justify-content: center;
}
.donut-valor { font-size: 1.4rem; font-weight: 800; color: var(--texto); line-height: 1; }
.donut-label { font-size: .6rem; font-weight: 700; color: var(--gris); }

.estatus-contenido { display: flex; flex-direction: column; align-items: center; }
.estatus-lista { width: 100%; }
.estatus-item {
  display: flex; align-items: center; gap: 8px;
  padding: 6px 0;
  border-bottom: 1px solid var(--borde);
  font-size: .82rem;
}
.estatus-item:last-child { border-bottom: none; }
.estatus-total { font-weight: 700; }
.estatus-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.estatus-nombre { flex: 1; font-weight: 600; color: var(--gris); font-size: .78rem; }
.estatus-num { font-weight: 700; color: var(--texto); }

/* Panel riesgo */
.badge-alerta {
  background: #FEF2F2; color: var(--rojo);
  border: 1px solid #FECACA;
  font-size: .7rem; font-weight: 700;
  padding: 3px 9px; border-radius: 20px;
  white-space: nowrap;
}
.riesgo-lista { display: flex; flex-direction: column; gap: 8px; }
.riesgo-item {
  display: grid;
  grid-template-columns: 26px 1fr auto;
  grid-template-rows: auto auto;
  gap: 0 10px;
  padding: 10px;
  border-radius: 8px;
  background: var(--fondo);
  border: 1px solid var(--borde);
  border-left: 3px solid var(--borde);
  align-items: center;
}
.riesgo-critico { border-left-color: var(--rojo); background: #FFF5F5; }
.riesgo-alto    { border-left-color: var(--amarillo); background: #FFFBEB; }
.riesgo-medio   { border-left-color: #60A5FA; background: #EFF6FF; }

.riesgo-rank {
  width: 26px; height: 26px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 800;
  grid-row: 1 / 3;
}
.riesgo-critico .riesgo-rank { background: var(--rojo); color: white; }
.riesgo-alto .riesgo-rank    { background: var(--amarillo); color: white; }
.riesgo-medio .riesgo-rank   { background: #3B82F6; color: white; }

.riesgo-info { grid-column: 2; }
.riesgo-nombre  { font-size: .82rem; font-weight: 700; color: var(--texto); margin: 0 0 2px; }
.riesgo-carrera { font-size: .72rem; color: var(--gris); margin: 0; font-weight: 600; }

.riesgo-metricas { grid-column: 3; text-align: right; }
.riesgo-pct { font-size: 1.1rem; font-weight: 800; color: var(--rojo); line-height: 1; }
.riesgo-sublabel { font-size: .65rem; color: var(--gris); font-weight: 600; }

.riesgo-barra-wrap {
  grid-column: 2 / 4;
  height: 4px; background: var(--borde);
  border-radius: 2px; overflow: hidden; margin-top: 4px;
}
.riesgo-barra {
  height: 100%; border-radius: 2px;
  transition: width .6s ease;
}
.riesgo-critico .riesgo-barra { background: var(--rojo); }
.riesgo-alto    .riesgo-barra { background: var(--amarillo); }
.riesgo-medio   .riesgo-barra { background: #3B82F6; }

/* Estado vacío */
.estado-vacio {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 2.5rem; gap: .75rem;
  color: var(--gris);
}
.vacio-icono { stroke: var(--verde); }
.estado-vacio p { font-size: .85rem; font-weight: 700; margin: 0; }

/* Skeletons genéricos */
.riesgo-skeleton { display: flex; flex-direction: column; gap: 8px; }
.skeleton-riesgo-item {
  height: 64px; border-radius: 8px;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
.estatus-skeleton { display: flex; justify-content: center; padding: 1rem 0; }
.donut-skeleton {
  width: 120px; height: 120px; border-radius: 50%;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
.tronco-skeleton { display: flex; flex-direction: column; gap: 10px; }

/* Panel tendencia */
.panel-tendencia { display: flex; flex-direction: column; }
.tendencia-contenido { flex: 1; display: flex; flex-direction: column; }
.tendencia-skeleton {
  height: 140px; border-radius: 8px;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
.linea-svg { width: 100%; height: 140px; }
.tendencia-etiquetas {
  display: flex; justify-content: space-between;
  margin-top: 4px;
}
.tend-label { font-size: .7rem; font-weight: 600; color: var(--gris); }

.tendencia-resumen {
  display: flex; gap: 1rem;
  margin-top: 1.2rem; padding-top: 1rem;
  border-top: 1px solid var(--borde);
}
.tend-stat { flex: 1; text-align: center; }
.tend-stat-val {
  display: block;
  font-size: 1.4rem; font-weight: 800; line-height: 1; margin-bottom: 4px;
}
.tend-stat-val.verde { color: var(--verde); }
.tend-stat-val.azul  { color: var(--azul); }
.tend-stat-lab { font-size: .7rem; font-weight: 700; color: var(--gris); }

/* ── Transición fade ── */
.fade-enter-active, .fade-leave-active { transition: opacity .25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Shimmer animation ── */
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ══ RESPONSIVE ══ */
@media (max-width: 1280px) {
  .fila-inferior { grid-template-columns: 260px 1fr 280px; }
}

@media (max-width: 1100px) {
  .graficas-grid  { grid-template-columns: 1fr; }
  .fila-inferior  { grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; }
  .panel-tendencia { grid-column: 1 / -1; }
}

@media (max-width: 768px) {
  .analytics-header { flex-direction: column; gap: .75rem; }
  .page-title { font-size: 1.35rem; }
  .header-right { width: 100%; }
  .filtros-panel { flex-direction: column; align-items: flex-start; }
  .filtros-controles { width: 100%; }
  .filtro-select { font-size: 16px; }
  .fila-inferior { grid-template-columns: 1fr; }
  .panel-tendencia { grid-column: 1; }
  
  .barras-parciales {
    grid-template-columns: repeat(2, 1fr); 
    height: auto;
    gap: 2rem;
  }
}

@media (max-width: 480px) {
  .btn-exportar { display: none; }
}
</style>