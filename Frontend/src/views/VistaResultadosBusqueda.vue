<script setup lang="ts">
/**
 * VistaResultadosBusqueda.vue — Vista completa de resultados de búsqueda
 * Ubicación: src/views/VistaResultadosBusqueda.vue
 * Sprint 1 · Equipo 3
 *
 * Ruta esperada: /busqueda?q=<termino>
 * Nombre:        ResultadosBusqueda
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter }             from 'vue-router'
import {
  Search, X, Loader2, AlertCircle,
  GraduationCap, User, Filter, ChevronLeft,
  UsersRound, SlidersHorizontal,
} from 'lucide-vue-next'
import TarjetaBusqueda, {
  type ResultadoBusqueda,
} from '@/components/search/TarjetaBusqueda.vue'
import Card from '../components/ui/Card.vue'

// ── Config ─────────────────────────────────────────────────────────────────
const API_BASE    = import.meta.env.VITE_API_URL ?? '/api'
const POR_PAGINA  = 24

// ── Router ─────────────────────────────────────────────────────────────────
const route  = useRoute()
const router = useRouter()

// ── Estado ─────────────────────────────────────────────────────────────────
type Filtro = 'todos' | 'alumnos' | 'docentes'

const termino    = ref('')
const cargando   = ref(false)
const error      = ref<string | null>(null)
const todos      = ref<ResultadoBusqueda[]>([])
const filtro     = ref<Filtro>('todos')
const pagina     = ref(1)

// ── Computados ─────────────────────────────────────────────────────────────
const filtrados = computed(() => {
  switch (filtro.value) {
    case 'alumnos':  return todos.value.filter(r => r.tipo === 'ALUMNO')
    case 'docentes': return todos.value.filter(r => r.tipo === 'DOCENTE')
    default:         return todos.value
  }
})

const totalAlumnos  = computed(() => todos.value.filter(r => r.tipo === 'ALUMNO').length)
const totalDocentes = computed(() => todos.value.filter(r => r.tipo === 'DOCENTE').length)

const paginados = computed(() =>
  filtrados.value.slice(0, pagina.value * POR_PAGINA)
)

const hayMas = computed(() =>
  paginados.value.length < filtrados.value.length
)

// ── Normalización ───────────────────────────────────────────────────────────
function normalizarAlumno(raw: Record<string, unknown>): ResultadoBusqueda {
  const ap = String(raw.apellido_paterno ?? raw.apaterno ?? '').toUpperCase()
  const am = String(raw.apellido_materno ?? raw.amaterno ?? '').toUpperCase()
  const nm = String(raw.nombre ?? raw.nombres ?? '').toUpperCase()
  return {
    tipo: 'ALUMNO',
    nombre_completo: [ap, am, nm].filter(Boolean).join(' '),
    identificador:   String(raw.numero_control ?? raw.num_control ?? raw.id ?? ''),
    area:            String(raw.carrera ?? raw.nom_carrera ?? ''),
    estatus:         String(raw.estatus ?? raw.status ?? '').toUpperCase(),
    id:              raw.id as number | string,
  }
}

function normalizarDocente(raw: Record<string, unknown>): ResultadoBusqueda {
  const ap = String(raw.apellido_paterno ?? raw.ap_paterno ?? '').toUpperCase()
  const am = String(raw.apellido_materno ?? raw.ap_materno ?? '').toUpperCase()
  const nm = String(raw.nombre ?? raw.nombres ?? '').toUpperCase()
  return {
    tipo: 'DOCENTE',
    nombre_completo: [ap, am, nm].filter(Boolean).join(' '),
    identificador:   String(raw.clave_docente ?? raw.clave ?? raw.numero_empleado ?? raw.id ?? ''),
    area:            String(raw.departamento ?? raw.nom_departamento ?? ''),
    estatus:         String(raw.estatus ?? raw.status ?? '').toUpperCase(),
    id:              raw.id as number | string,
  }
}

// ── Búsqueda ────────────────────────────────────────────────────────────────
let ctrl: AbortController | null = null

async function buscar(q: string) {
  if (!q.trim()) return

  ctrl?.abort()
  ctrl = new AbortController()
  const { signal } = ctrl

  cargando.value = true
  error.value    = null
  todos.value    = []
  pagina.value   = 1

  try {
    const [ra, rd] = await Promise.allSettled([
      fetch(`${API_BASE}/buscar-alumno?q=${encodeURIComponent(q)}`, { signal }),
      fetch(`${API_BASE}/personas/buscar?q=${encodeURIComponent(q)}`, { signal }),
    ])

    const lista: ResultadoBusqueda[] = []

    if (ra.status === 'fulfilled' && ra.value.ok) {
      const json = await ra.value.json()
      const arr: Record<string, unknown>[] = Array.isArray(json) ? json : json?.data ?? []
      arr.forEach(r => lista.push(normalizarAlumno(r)))
    }
    if (rd.status === 'fulfilled' && rd.value.ok) {
      const json = await rd.value.json()
      const arr: Record<string, unknown>[] = Array.isArray(json) ? json : json?.data ?? []
      arr.forEach(r => lista.push(normalizarDocente(r)))
    }

    lista.sort((a, b) => a.nombre_completo.localeCompare(b.nombre_completo))
    todos.value = lista
  } catch (e: unknown) {
    if ((e as Error).name === 'AbortError') return
    error.value = 'ERROR DE CONEXIÓN CON EL SERVIDOR'
  } finally {
    cargando.value = false
  }
}

// ── Sincronizar con query param ─────────────────────────────────────────────
function sincronizar() {
  const q = String(route.query.q ?? '').trim()
  if (q !== termino.value) {
    termino.value = q
    filtro.value  = 'todos'
    pagina.value  = 1
  }
  if (q) buscar(q)
}

watch(() => route.query.q, sincronizar)
onMounted(sincronizar)

// ── Acciones ────────────────────────────────────────────────────────────────
function seleccionar(r: ResultadoBusqueda) {
  const ruta = r.tipo === 'ALUMNO'
    ? `/alumnos/${r.id ?? r.identificador}`
    : `/docentes/${r.id ?? r.identificador}`
  router.push(ruta)
}

function setBadgeFiltro(f: Filtro) {
  filtro.value = f
  pagina.value = 1
}

// ── KPIs de carga ─── (skeleton count)
const skeletonCount = 12
</script>

<template>
  <div class="vista-resultados">

    <!-- ══ CABECERA ══════════════════════════════════════════ -->
    <div class="cabecera">
      <button type="button" class="btn-volver" @click="router.back()">
        <ChevronLeft :size="16" />
        VOLVER
      </button>

      <div class="cabecera-titulo">
        <UsersRound :size="20" class="text-primary-500" />
        <h1 class="titulo">RESULTADOS DE BÚSQUEDA</h1>
      </div>

      <!-- Termino actual -->
      <p class="subtitulo" v-if="termino">
        MOSTRANDO RESULTADOS PARA:
        <strong class="text-primary-600 dark:text-primary-400">"{{ termino.toUpperCase() }}"</strong>
      </p>
    </div>

    <!-- ══ BARRA DE FILTROS ═══════════════════════════════════ -->
    <div class="barra-filtros">
      <div class="filtros-grupo">
        <SlidersHorizontal :size="14" class="text-surface-400" />
        <span class="label-filtros">FILTRAR POR:</span>

        <button
          v-for="f in (['todos', 'alumnos', 'docentes'] as const)"
          :key="f"
          type="button"
          class="btn-filtro"
          :class="{ 'btn-filtro-activo': filtro === f }"
          @click="setBadgeFiltro(f)"
        >
          <component
            :is="f === 'alumnos' ? GraduationCap : f === 'docentes' ? User : Filter"
            :size="12"
          />
          {{ f === 'todos' ? 'TODOS' : f === 'alumnos' ? `ALUMNOS (${totalAlumnos})` : `DOCENTES (${totalDocentes})` }}
        </button>
      </div>

      <!-- Contador de resultados -->
      <p v-if="!cargando" class="texto-conteo">
        {{ filtrados.length }}
        {{ filtrados.length === 1 ? 'RESULTADO' : 'RESULTADOS' }}
      </p>
    </div>

    <!-- ══ CUERPO ══════════════════════════════════════════════ -->

    <!-- Error -->
    <Card v-if="error" variante="outlined" clase="border-red-300 dark:border-red-800 mt-4">
      <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
        <AlertCircle :size="20" />
        <p class="text-sm font-semibold uppercase tracking-wide">{{ error }}</p>
      </div>
    </Card>

    <!-- Grid de resultados -->
    <div v-else-if="!cargando && filtrados.length > 0" class="grid-resultados">
      <TarjetaBusqueda
        v-for="r in paginados"
        :key="`${r.tipo}-${r.identificador}`"
        :resultado="r"
        :termino="termino"
        class="card-resultado"
        @seleccionar="seleccionar"
      />
    </div>

    <!-- Skeleton (cargando) -->
    <div v-else-if="cargando" class="grid-resultados">
      <div
        v-for="i in skeletonCount"
        :key="i"
        class="skeleton-card animate-pulse"
      >
        <div class="w-8 h-8 rounded-lg bg-surface-200 dark:bg-surface-700 flex-shrink-0" />
        <div class="flex-1 space-y-2">
          <div class="h-3 w-3/4 rounded bg-surface-200 dark:bg-surface-700" />
          <div class="h-2.5 w-1/2 rounded bg-surface-100 dark:bg-surface-800" />
        </div>
      </div>
    </div>

    <!-- Sin resultados -->
    <div v-else-if="!cargando && termino" class="estado-vacio">
      <Search :size="40" class="text-surface-300 dark:text-surface-600 mx-auto mb-3" />
      <p class="text-base font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wide">
        SIN RESULTADOS
      </p>
      <p class="text-sm text-surface-400 dark:text-surface-500 mt-1">
        NO SE ENCONTRARON REGISTROS PARA "{{ termino.toUpperCase() }}"
      </p>
    </div>

    <!-- ══ CARGAR MÁS ════════════════════════════════════════ -->
    <div v-if="hayMas && !cargando" class="cargar-mas">
      <button type="button" class="btn-cargar-mas" @click="pagina++">
        <Loader2 v-if="false" :size="15" class="animate-spin" />
        CARGAR MÁS ({{ filtrados.length - paginados.length }} RESTANTES)
      </button>
    </div>

  </div>
</template>

<style scoped>
/* ── Layout ────────────────────────────────────────────── */
.vista-resultados {
  @apply max-w-5xl mx-auto px-4 py-6 space-y-4;
}

/* ── Cabecera ──────────────────────────────────────────── */
.cabecera {
  @apply space-y-2;
}
.btn-volver {
  @apply flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider
         text-surface-500 hover:text-primary-600 dark:text-surface-400 dark:hover:text-primary-400
         transition-colors;
}
.cabecera-titulo {
  @apply flex items-center gap-2;
}
.titulo {
  @apply text-xl font-display font-bold text-surface-900 dark:text-surface-50
         uppercase tracking-wide;
}
.subtitulo {
  @apply text-sm text-surface-500 dark:text-surface-400 uppercase tracking-wide;
}

/* ── Filtros ───────────────────────────────────────────── */
.barra-filtros {
  @apply flex items-center justify-between gap-3 flex-wrap
         bg-white dark:bg-surface-800
         border border-surface-200 dark:border-surface-700
         rounded-xl px-4 py-3 shadow-sm;
}
.filtros-grupo {
  @apply flex items-center gap-2 flex-wrap;
}
.label-filtros {
  @apply text-[11px] font-bold text-surface-400 dark:text-surface-500 uppercase tracking-widest mr-1;
}
.btn-filtro {
  @apply flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider
         px-3 py-1.5 rounded-lg border transition-all duration-150
         border-surface-200 dark:border-surface-700
         text-surface-600 dark:text-surface-400
         hover:bg-surface-100 dark:hover:bg-surface-700;
}
.btn-filtro-activo {
  @apply bg-primary-500 text-white border-primary-500
         dark:bg-primary-600 dark:border-primary-600
         hover:bg-primary-600 dark:hover:bg-primary-700;
}
.texto-conteo {
  @apply text-[11px] font-bold uppercase tracking-wider
         text-surface-500 dark:text-surface-400;
}

/* ── Grid ──────────────────────────────────────────────── */
.grid-resultados {
  @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2;
}
.card-resultado {
  @apply bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700
         rounded-xl shadow-sm hover:shadow-md transition-all duration-150
         hover:-translate-y-0.5;
}

/* ── Skeleton ──────────────────────────────────────────── */
.skeleton-card {
  @apply flex items-center gap-3 p-4
         bg-white dark:bg-surface-800
         border border-surface-200 dark:border-surface-700
         rounded-xl;
}

/* ── Estado vacío ──────────────────────────────────────── */
.estado-vacio {
  @apply text-center py-16;
}

/* ── Cargar más ────────────────────────────────────────── */
.cargar-mas {
  @apply flex justify-center pt-2;
}
.btn-cargar-mas {
  @apply flex items-center gap-2 px-6 py-2.5 rounded-xl
         border border-primary-300 dark:border-primary-700
         text-[11px] font-bold uppercase tracking-wider
         text-primary-600 dark:text-primary-400
         hover:bg-primary-50 dark:hover:bg-primary-950/60
         transition-all duration-150;
}
</style>