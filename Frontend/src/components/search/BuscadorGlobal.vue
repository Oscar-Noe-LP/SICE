<script setup lang="ts">
/**
 * BuscadorGlobal.vue — Búsqueda Inteligente Unificada del SICE
 * Ubicación: src/components/search/BuscadorGlobal.vue
 * Sprint 1 · Equipo 3
 */

import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { Search, X, Loader2, AlertCircle, Users } from 'lucide-vue-next'

import TarjetaBusqueda, {
  type ResultadoBusqueda,
} from '@/components/search/TarjetaBusqueda.vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = withDefaults(
  defineProps<{ tema?: 'default' | 'header' }>(),
  { tema: 'default' }
)
const enHeader = computed(() => props.tema === 'header')

// ── Declaración de Emits para acoplar con DetalleUsuario ──────────────────────
const emit = defineEmits<{
  seleccionar: [usuario: ResultadoBusqueda]
}>()

// ── Config ────────────────────────────────────────────────────────────────────
const API_BASE =
  import.meta.env.VITE_API_URL?.includes('/api')
    ? import.meta.env.VITE_API_URL
    : `${import.meta.env.VITE_API_URL}/api`
const DEBOUNCE_MS = 300
const MAX_RESULTS = 20

const ENDPOINTS = {
  alumnos: {
    principal: (q: string) => `${API_BASE}/buscar-alumno?q=${encodeURIComponent(q)}`,
    fallback:  (q: string) => `${API_BASE}/alumnos/buscar-control?q=${encodeURIComponent(q)}`,
  },
  docentes: {
    principal: (q: string) => `${API_BASE}/personas/buscar?q=${encodeURIComponent(q)}`,
    fallback:  (q: string) => `${API_BASE}/docentes?q=${encodeURIComponent(q)}`,
  },
}

// ── Estado ────────────────────────────────────────────────────────────────────
const router     = useRouter()
const inputRef   = ref<HTMLInputElement | null>(null)
const wrapperRef = ref<HTMLDivElement  | null>(null)

const termino      = ref('')
const cargando     = ref(false)
const error        = ref<string | null>(null)
const resultados   = ref<ResultadoBusqueda[]>([])
const abierto      = ref(false)
const indiceActivo = ref(-1)
const focusado     = ref(false)

let debounceTimer: ReturnType<typeof setTimeout> | null = null
let abortController: AbortController | null = null

// ── Computados ────────────────────────────────────────────────────────────────
const hayTermino    = computed(() => termino.value.trim().length >= 2)
const hayResultados = computed(() => resultados.value.length > 0)
const sinResultados = computed(() =>
  !cargando.value && hayTermino.value && resultados.value.length === 0 && !error.value
)
const activo = computed(() => focusado.value || !!termino.value)

// ── Normalización ─────────────────────────────────────────────────────────────

function normalizarAlumno(raw: any): ResultadoBusqueda {
  return {
    id: raw.numero_control,

    tipo: 'ALUMNO',

    nombre_completo: String(
      raw.nombre_completo ??
      raw.nombre ??
      ''
    ).toUpperCase(),

    identificador: String(
      raw.numero_control ??
      ''
    ),

    area: String(
      raw.carrera ??
      ''
    ),

    estatus: String(
      raw.estatus ??
      'ACTIVO'
    ),

    original: raw
  } as ResultadoBusqueda
}

function normalizarDocente(raw: Record<string, unknown>): ResultadoBusqueda {
  console.log('DOCENTE RAW:', raw)

  return {
    tipo: 'DOCENTE',

    nombre_completo: String(
      raw.nombre_completo ??
      raw.nombre ??
      ''
    ).toUpperCase(),

    identificador: String(
      raw.curp ??
      raw.id_persona ??
      raw.id ??
      ''
    ),

    area: 'PERSONAL DOCENTE',

    estatus: 'ACTIVO',

    id: Number(
      raw.id_persona ??
      raw.id ??
      0
    ),
  } as ResultadoBusqueda
}

// ── Fetch con fallback automático y Autenticación ────────────────────────────
async function fetchConFallback(
  principal: string,
  fallback: string,
  signal: AbortSignal
): Promise<Record<string, unknown>[]> {
  const token = localStorage.getItem('token')

  const opcionesFetch: RequestInit = {
    signal,
    headers: {
      'Accept': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {})
    }
  }

  let res: Response
  try {
    res = await fetch(principal, opcionesFetch)
    if (!res.ok) {
      console.warn(`[Buscador] ${principal} devolvió ${res.status}, intentando fallback...`)
      res = await fetch(fallback, opcionesFetch)
    }
  } catch (e) {
    if ((e as Error).name === 'AbortError') throw e
    console.warn(`[Buscador] Error de red en principal, intentando fallback...`)
    try {
      res = await fetch(fallback, opcionesFetch)
    } catch (fallbackError) {
      if ((fallbackError as Error).name === 'AbortError') throw fallbackError
      return []
    }
  }

 if (!res.ok) return []

try {
  const json = await res.json()

  if (Array.isArray(json))            return json
  if (Array.isArray(json?.data))      return json.data
  if (Array.isArray(json?.alumnos))   return json.alumnos
  if (Array.isArray(json?.docentes))  return json.docentes
  if (Array.isArray(json?.personas))  return json.personas
  if (Array.isArray(json?.empleados)) return json.empleados

  // Cuando la API devuelve UN SOLO objeto
  if (json && typeof json === 'object') {
    return [json]
  }

  return []

} catch {
  return []
}
}

// ── Búsqueda principal ────────────────────────────────────────────────────────
async function buscar(q: string) {
  if (!q || q.trim().length < 2) {
    resultados.value = []
    abierto.value    = false
    return
  }

  abortController?.abort()
  abortController = new AbortController()
  const { signal } = abortController

  cargando.value     = true
  error.value        = null
  abierto.value      = true
  indiceActivo.value = -1

  try {
    const [resAlumnos, resDocentes] = await Promise.allSettled([
      fetchConFallback(ENDPOINTS.alumnos.principal(q), ENDPOINTS.alumnos.fallback(q), signal),
      fetchConFallback(ENDPOINTS.docentes.principal(q), ENDPOINTS.docentes.fallback(q), signal),
    ])

    const lista: ResultadoBusqueda[] = []

    const idsAlumnos = new Set<number>()

    if (resAlumnos.status === 'fulfilled') {
      resAlumnos.value.slice(0, MAX_RESULTS).forEach(r => {
        lista.push(normalizarAlumno(r))
        idsAlumnos.add(Number(r.id_persona))
      })
    }

    if (resDocentes.status === 'fulfilled') {
      resDocentes.value
        .filter(r => !idsAlumnos.has(Number(r.id_persona))) // ← descarta si ya está como alumno
        .slice(0, MAX_RESULTS)
        .forEach(r => lista.push(normalizarDocente(r)))
    }

    if (lista.length === 0 && resAlumnos.status === 'rejected' && resDocentes.status === 'rejected') {
      error.value = 'ERROR AL CONECTAR CON EL SERVIDOR'
    }

    lista.sort((a, b) => {
      if (a.tipo !== b.tipo) return a.tipo === 'ALUMNO' ? -1 : 1
      return a.nombre_completo.localeCompare(b.nombre_completo, 'es', { sensitivity: 'base' })
    })

    resultados.value = lista.slice(0, MAX_RESULTS)
  } catch (e: unknown) {
    if ((e as Error).name === 'AbortError') return
    console.error('[BuscadorGlobal]', e)
    error.value = 'ERROR AL CONECTAR CON EL SERVIDOR'
  } finally {
    cargando.value = false
  }
}

// ── Debounce ──────────────────────────────────────────────────────────────────
watch(termino, (val) => {
  if (debounceTimer) clearTimeout(debounceTimer)
  if (!val || val.trim().length < 2) {
    resultados.value = []
    abierto.value    = false
    cargando.value   = false
    return
  }
  cargando.value = true
  debounceTimer = setTimeout(() => buscar(val.trim()), DEBOUNCE_MS)
})

// ── Teclado ───────────────────────────────────────────────────────────────────
function onKeydown(e: KeyboardEvent) {
  if (!abierto.value) return
  const n = resultados.value.length
  switch (e.key) {
    case 'ArrowDown': e.preventDefault(); indiceActivo.value = (indiceActivo.value + 1) % n; break
    case 'ArrowUp':   e.preventDefault(); indiceActivo.value = (indiceActivo.value - 1 + n) % n; break
    case 'Enter': {
  e.preventDefault()

  const seleccionado = resultados.value[indiceActivo.value]

  if (indiceActivo.value >= 0 && seleccionado) {
    seleccionar(seleccionado)
  }

  break
}
    case 'Escape': cerrar(); break
  }
}
function seleccionar(r: ResultadoBusqueda) {
  cerrar()

  if (r.tipo === 'ALUMNO') {
    // Navega a la vista de alumnos con el query param que abre el modal directamente
    router.push({ name: 'AlumnosGestion', query: { ver: r.identificador } })
    return
  }

  if (r.tipo === 'DOCENTE') {
    router.push({ name: 'DetallePersona', params: { id: r.id } })
    return
  }

  emit('seleccionar', r)
}
function verTodos() {
  cerrar()
  router.push({ name: 'ResultadosBusqueda', query: { q: termino.value.trim() } })
}
function limpiar() {
  termino.value = ''; resultados.value = []; abierto.value = false
  error.value = null; cargando.value = false
  inputRef.value?.focus()
}
function cerrar() {
  abierto.value = false; indiceActivo.value = -1; inputRef.value?.blur()
}
function onClickFuera(e: MouseEvent) {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target as Node)) cerrar()
}
function onGlobalKey(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault(); inputRef.value?.focus(); inputRef.value?.select()
  }
}

onMounted(() => {
  document.addEventListener('mousedown', onClickFuera)
  window.addEventListener('keydown', onGlobalKey)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onClickFuera)
  window.removeEventListener('keydown', onGlobalKey)
  abortController?.abort()
  if (debounceTimer) clearTimeout(debounceTimer)
})
</script>

<template>
  <div
    ref="wrapperRef"
    class="buscador-wrapper"
    :class="enHeader ? 'buscador-header' : 'buscador-default'"
    role="combobox"
    :aria-expanded="abierto"
  >
    <div
      class="buscador-input-wrapper"
      :class="[
        enHeader
          ? activo ? 'wrapper-header-activo' : 'wrapper-header-reposo'
          : activo ? 'wrapper-default-activo' : 'wrapper-default-reposo'
      ]"
    >
      <span class="icono-busqueda" :class="enHeader && !activo ? 'icono-blanco' : 'icono-gris'">
        <Search :size="16" stroke-width="2.5" />
      </span>

      <input
        ref="inputRef"
        v-model="termino"
        type="search"
        autocomplete="off"
        spellcheck="false"
        class="buscador-input"
        :class="enHeader && !activo ? 'input-blanco' : 'input-oscuro'"
        placeholder="BUSCAR ALUMNO O DOCENTE..."
        aria-label="BÚSQUEDA GLOBAL"
        aria-autocomplete="list"
        aria-controls="sice-search-dropdown"
        @keydown="onKeydown"
        @focus="focusado = true; termino.length >= 2 && (abierto = true)"
        @blur="focusado = false"
      />

      <span class="icono-derecha">
        <Loader2
          v-if="cargando"
          :size="14"
          class="animate-spin"
          :class="enHeader && !activo ? 'text-white/70' : 'spin-azul'"
        />
        <button
          v-else-if="termino"
          type="button"
          class="btn-limpiar"
          :class="enHeader && !activo ? 'btn-limpiar-blanco' : 'btn-limpiar-gris'"
          aria-label="LIMPIAR BÚSQUEDA"
          @click="limpiar"
        >
          <X :size="13" stroke-width="2.5" />
        </button>
        <kbd v-else class="kbd-hint" :class="enHeader && !activo ? 'kbd-header' : 'kbd-default'">
          Ctrl K
        </kbd>
      </span>
    </div>

    <Transition name="dropdown">
      <div v-if="abierto" id="sice-search-dropdown" class="dropdown-panel" role="listbox">
        <template v-if="hayResultados">
          <div class="grid-resultados">
 <TarjetaBusqueda
  v-for="resultado in resultados"
  :key="resultado.id"
  :resultado="resultado"
  :termino="termino"
  @seleccionar="seleccionar"
/>
</div>
        </template>

        <div v-else-if="sinResultados" class="estado-panel">
          <Search :size="26" class="mx-auto mb-2" style="color:#D1D5DB" />
          <p class="texto-estado-titulo">SIN RESULTADOS</p>
          <p class="texto-estado-sub">NO SE ENCONTRÓ "{{ termino.trim().toUpperCase() }}"</p>
        </div>

        <div v-else-if="error" class="estado-panel">
          <AlertCircle :size="20" class="mx-auto mb-1.5" style="color:#F87171" />
          <p class="texto-estado-titulo" style="color:#DC2626">{{ error }}</p>
          <p class="texto-estado-sub">VERIFICA LA CONEXIÓN CON EL SERVIDOR</p>
        </div>

        <div v-else-if="cargando" class="estado-cargando">
          <div v-for="i in 3" :key="i" class="skeleton-row animate-pulse">
            <div class="skeleton-avatar" />
            <div style="flex:1;display:flex;flex-direction:column;gap:6px">
              <div class="skeleton-line" style="width:70%" />
              <div class="skeleton-line-light" style="width:45%" />
            </div>
            <div class="skeleton-badge" />
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>

.buscador-wrapper {
  position: relative;
  width: 100%;
}

.buscador-header {
  max-width: 460px;
}

.buscador-default {
  max-width: 620px;
}

.buscador-input-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;

  height: 52px;

  padding: 0 16px;

  border-radius: 14px;

  border: 1.5px solid transparent;

  transition:
    background .25s ease,
    border-color .25s ease,
    box-shadow .25s ease,
    transform .25s ease;
}

.wrapper-header-reposo {
  background: rgba(255,255,255,0.13);
  border-color: rgba(255,255,255,0.22);
}

.wrapper-header-reposo:hover {
  background: rgba(255,255,255,0.20);
  border-color: rgba(255,255,255,0.38);
}

.wrapper-header-activo {
  background: #ffffff;
  border-color: #ffffff;

  box-shadow:
    0 0 0 4px rgba(255,255,255,0.20),
    0 8px 24px rgba(0,0,0,.08);
}

.wrapper-default-reposo {
  background: #ffffff;

  border-color: #E2E8F0;

  box-shadow:
    0 2px 6px rgba(0,0,0,.04);
}

.wrapper-default-activo {
  background: #ffffff;

  border-color: #1D52B7;

  box-shadow:
    0 0 0 4px rgba(29,82,183,.10),
    0 10px 24px rgba(29,82,183,.08);
}

.buscador-input {
  flex: 1;
  min-width: 0;

  background: transparent;
  border: none;
  outline: none;

  font-size: .90rem;

  font-weight: 600;

  letter-spacing: .05em;

  text-transform: uppercase;

  font-family: 'Montserrat', sans-serif;

  -webkit-appearance: none;
  appearance: textfield;
}

.buscador-input::-webkit-search-cancel-button,
.buscador-input::-webkit-search-decoration {
  display: none;
}

.input-blanco {
  color: #ffffff;
}

.input-blanco::placeholder {
  color: rgba(255,255,255,.55);
}

.input-oscuro {
  color: #1a1a2e;
}

.input-oscuro::placeholder {
  color: #9CA3AF;
}

.icono-busqueda {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.icono-blanco {
  color: rgba(255,255,255,.75);
}

.icono-gris {
  color: #6B7280;
}

.icono-derecha {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  height: 100%;
}

.spin-azul {
  color: #1D52B7;
}

.btn-limpiar {
  display: flex;
  align-items: center;
  justify-content: center;

  border: none;
  background: none;

  cursor: pointer;

  padding: 4px;

  border-radius: 6px;

  transition: all .2s ease;
}

.btn-limpiar-blanco {
  color: rgba(255,255,255,.70);
}

.btn-limpiar-blanco:hover {
  color: #ffffff;
}

.btn-limpiar-gris {
  color: #9CA3AF;
}

.btn-limpiar-gris:hover {
  color: #374151;
}

.kbd-hint {
  font-size: 10px;
  font-weight: 600;
  font-family: monospace;

  border-radius: 6px;

  padding: 3px 8px;

  white-space: nowrap;

  border: 1px solid;

  user-select: none;
}

.kbd-header {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.22);
  color: rgba(255,255,255,.60);
}

.kbd-default {
  background: #F3F4F6;
  border-color: #E5E7EB;
  color: #9CA3AF;
}

/* ======================
   DROPDOWN PRINCIPAL
====================== */

.dropdown-panel {
  position: absolute;

  top: calc(100% + 8px);

  left: 50%;
  transform: translateX(-50%);

  z-index: 9999;

  background: #ffffff;

  border: 1px solid #E5E7EB;

  border-radius: 16px;

  overflow: hidden;

  width: max-content;
  max-width: min(600px, 96vw);
  min-width: min(480px, 96vw);

  box-shadow:
    0 24px 64px rgba(11,37,69,.12),
    0 8px 24px rgba(11,37,69,.08);
}

.dropdown-lista {
  max-height: 460px;

  overflow-y: auto;
  overflow-x: hidden;

  padding: 8px;

  display: flex;
  flex-direction: column;

  gap: 6px;
}

/* Scrollbar del design system (main.css) */
.grid-resultados::-webkit-scrollbar,
.dropdown-lista::-webkit-scrollbar          { width: 6px; height: 6px; }
.grid-resultados::-webkit-scrollbar-track,
.dropdown-lista::-webkit-scrollbar-track    { background: transparent; }
.grid-resultados::-webkit-scrollbar-thumb,
.dropdown-lista::-webkit-scrollbar-thumb    { background: #c8cfd9; border-radius: 99px; }
.grid-resultados::-webkit-scrollbar-thumb:hover,
.dropdown-lista::-webkit-scrollbar-thumb:hover { background: #9aa3b0; }

.grid-resultados{
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 8px;
  max-height: 420px;
  overflow-y: auto;
  overflow-x: hidden;
}

.resultado-activo {
  background: #EFF6FF;

  outline: 1px solid #BFDBFE;

  border-radius: 12px;
}

.dropdown-footer {
  border-top: 1px solid #F1F5F9;

  padding: 12px;

  background: #FAFBFC;
}

.btn-ver-todos {
  width: 100%;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 8px;

  padding: 12px;

  border: none;

  background: transparent;

  cursor: pointer;

  border-radius: 10px;

  font-size: 11px;

  font-weight: 700;

  letter-spacing: .08em;

  text-transform: uppercase;

  color: #1D52B7;

  font-family: 'Montserrat', sans-serif;

  transition: all .25s ease;
}

.btn-ver-todos:hover {
  background: rgba(29,82,183,.08);
}

/* ======================
   ESTADOS
====================== */

.estado-panel {
  padding: 40px 24px;

  text-align: center;
}

.texto-estado-titulo {
  font-size: .82rem;

  font-weight: 700;

  text-transform: uppercase;

  letter-spacing: .06em;

  color: #6B7280;

  margin: 0;
}

.texto-estado-sub {
  font-size: .75rem;

  color: #9CA3AF;

  margin: 6px 0 0;

  text-transform: uppercase;

  letter-spacing: .04em;
}

/* ======================
   LOADING
====================== */

.estado-cargando {
  padding: 12px;
}

.skeleton-row {
  display: flex;
  align-items: center;

  gap: 12px;

  padding: 12px;

  border-radius: 12px;
}

.skeleton-avatar {
  width: 40px;
  height: 40px;

  border-radius: 12px;

  background: #E2E8F0;

  flex-shrink: 0;
}

.skeleton-line {
  height: 12px;

  border-radius: 4px;

  background: #E2E8F0;
}

.skeleton-line-light {
  height: 10px;

  border-radius: 4px;

  background: #F1F5F9;
}

.skeleton-badge {
  width: 54px;
  height: 18px;

  border-radius: 999px;

  background: #E2E8F0;

  flex-shrink: 0;
}

/* ======================
   ANIMACIÓN
====================== */

.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity 180ms ease,
    transform 180ms ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-6px) scale(.98);
}

</style>