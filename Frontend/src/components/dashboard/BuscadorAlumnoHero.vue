<script setup lang="ts">
/**
 * BuscadorAlumnoHero.vue — Búsqueda rápida de alumnos con autocomplete
 * Ubicación sugerida: src/components/dashboard/BuscadorAlumnoHero.vue
 *
 * Usa el mismo endpoint que BuscadorGlobal pero solo para alumnos,
 * adaptado visualmente al hero oscuro del dashboard.
 */

import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'

// ── Config ─────────────────────────────────────────────────────────────
const API_URL = import.meta.env.VITE_API_URL
const API_BASE = API_URL?.includes('/api') ? API_URL : `${API_URL}/api`
const DEBOUNCE_MS = 300

// ── Estado ─────────────────────────────────────────────────────────────
const router     = useRouter()
const wrapperRef = ref<HTMLDivElement | null>(null)
const inputRef   = ref<HTMLInputElement | null>(null)

const termino      = ref('')
const resultados   = ref<any[]>([])
const cargando     = ref(false)
const error        = ref<string | null>(null)
const abierto      = ref(false)
const indiceActivo = ref(-1)

let debounceTimer: ReturnType<typeof setTimeout> | null = null
let abortController: AbortController | null = null

// ── Computados ─────────────────────────────────────────────────────────
const hayResultados = computed(() => resultados.value.length > 0)
const sinResultados = computed(() =>
  !cargando.value && termino.value.trim().length >= 2 && resultados.value.length === 0 && !error.value
)
const esValida = computed(() => {
  const t = termino.value.trim()
  if (!t) return false
  return /^\d+$/.test(t) ? t.length === 8 : t.length >= 3
})

// ── Fetch ───────────────────────────────────────────────────────────────
async function buscar(q: string) {
  if (!q || q.trim().length < 2) {
    resultados.value = []
    abierto.value    = false
    return
  }

  abortController?.abort()
  abortController    = new AbortController()
  const { signal }   = abortController
  cargando.value     = true
  error.value        = null
  abierto.value      = true
  indiceActivo.value = -1

  const token = localStorage.getItem('token')
  const headers: Record<string, string> = { Accept: 'application/json' }
  if (token) headers['Authorization'] = `Bearer ${token}`

  try {
    const esNumero = /^\d+$/.test(q)

    // ── Número de control: usa el endpoint de búsqueda por control ──
    if (esNumero) {
      const res  = await fetch(`${API_BASE}/alumnos/buscar-control?no_control=${encodeURIComponent(q)}`, { signal, headers })
      if (res.status === 404) { resultados.value = []; return }
      const json = await res.json()
      resultados.value = (json.resultados ?? []).slice(0, 8)  // ← mismo formato ahora
      return
    }

    // ── Nombre o apellido: endpoint existente ───────────────────────
    const res  = await fetch(`${API_BASE}/kardex/buscar-por-nombre?q=${encodeURIComponent(q)}`, { signal, headers })
    const json = await res.json()

    if (res.status === 404) { resultados.value = []; return }
    if (!res.ok) { error.value = json.error ?? 'Error del servidor'; return }

    resultados.value = (json.resultados ?? []).slice(0, 8)

  } catch (e: any) {
    if (e.name === 'AbortError') return
    error.value = 'Error al conectar con el servidor'
  } finally {
    cargando.value = false
  }
}

// ── Debounce ────────────────────────────────────────────────────────────
watch(termino, (val) => {
  if (debounceTimer) clearTimeout(debounceTimer)
  const t = val.trim()
  const esNumero = /^\d+$/.test(t)
  const minimoCaracteres = esNumero ? 4 : 2  // 4 dígitos para control, 2 letras para nombre

  if (!t || t.length < minimoCaracteres) {
    resultados.value = []
    abierto.value    = false
    cargando.value   = false
    return
  }
  cargando.value = true
  debounceTimer = setTimeout(() => buscar(t), DEBOUNCE_MS)
})
// ── Navegación por teclado ──────────────────────────────────────────────
function onKeydown(e: KeyboardEvent) {
  const n = resultados.value.length

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    indiceActivo.value = (indiceActivo.value + 1) % n
    return
  }
  if (e.key === 'ArrowUp') {
    e.preventDefault()
    indiceActivo.value = (indiceActivo.value - 1 + n) % n
    return
  }
  if (e.key === 'Escape') {
    cerrar()
    return
  }
  if (e.key === 'Enter') {
    e.preventDefault()
    if (indiceActivo.value >= 0 && resultados.value[indiceActivo.value]) {
      seleccionar(resultados.value[indiceActivo.value])
    } else {
      irAKardex()
    }
  }
}

// ── Acciones ────────────────────────────────────────────────────────────
function seleccionar(alumno: any) {
  const nc = alumno.numero_control ?? alumno.identificador ?? alumno.id
  cerrar()
  if (nc) router.push(`/kardex/${nc}`)
}

function irAKardex() {
  if (!esValida.value) return
  const t = termino.value.trim()

  // Si es número de control directo, navega sin buscar
  if (/^\d{8}$/.test(t)) {
    cerrar()
    router.push(`/kardex/${t}`)
    return
  }

  // Si hay resultados, navega al primero
  if (resultados.value.length > 0) {
    seleccionar(resultados.value[0])
    return
  }

  // Fallback: lanza búsqueda y espera resultado
  buscar(t).then(() => {
    if (resultados.value.length > 0) seleccionar(resultados.value[0])
    else error.value = 'No se encontraron alumnos con ese criterio'
  })
}

function limpiar() {
  termino.value    = ''
  resultados.value = []
  abierto.value    = false
  error.value      = null
  cargando.value   = false
  inputRef.value?.focus()
}

function cerrar() {
  abierto.value      = false
  indiceActivo.value = -1
}

// ── Cierre al click fuera ───────────────────────────────────────────────
function onClickFuera(e: MouseEvent) {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target as Node)) cerrar()
}

// ── Iniciales del alumno para el avatar ────────────────────────────────
function iniciales(alumno: any): string {
  const nombre = alumno.nombre_completo ?? alumno.nombre ?? ''
  return nombre.trim().split(/\s+/).slice(0, 2).map((p: string) => p[0]).join('').toUpperCase() || '?'
}

function nombreCompleto(alumno: any): string {
  return (alumno.nombre_completo ?? alumno.nombre ?? '—').toUpperCase()
}

function numeroControl(alumno: any): string {
  return alumno.numero_control ?? alumno.identificador ?? '—'
}

function carrera(alumno: any): string {
  return (alumno.carrera ?? alumno.area ?? '').toUpperCase()
}

function estatusClass(alumno: any): string {
  const e = (alumno.estatus ?? '').toLowerCase()
  if (e.includes('activ'))   return 'est-activo'
  if (e.includes('baja'))    return 'est-baja'
  if (e.includes('egres'))   return 'est-egresado'
  return 'est-otro'
}

onMounted(() => document.addEventListener('mousedown', onClickFuera))
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onClickFuera)
  abortController?.abort()
  if (debounceTimer) clearTimeout(debounceTimer)
})
</script>

<template>
  <div ref="wrapperRef" class="bah-wrapper">

    <!-- Input + botón -->
    <div class="bah-form">
      <div class="bah-input-wrap" :class="{ 'bah-input-wrap--activo': abierto || termino }">
        <!-- Icono lupa -->
        <svg class="bah-ico-lupa" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>

        <input
          ref="inputRef"
          v-model.trim="termino"
          class="bah-input"
          type="search"
          autocomplete="off"
          spellcheck="false"
          placeholder="NÚMERO DE CONTROL, NOMBRE O APELLIDO"
          aria-label="Búsqueda de alumno"
          aria-autocomplete="list"
          @keydown="onKeydown"
          @focus="termino.length >= 2 && (abierto = true)"
        />

        <!-- Spinner / limpiar -->
        <span class="bah-ico-der">
          <svg v-if="cargando" class="bah-spinner" xmlns="http://www.w3.org/2000/svg"
            width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83"/>
          </svg>
          <button v-else-if="termino" class="bah-btn-x" type="button" aria-label="Limpiar" @click="limpiar">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </span>
      </div>

      <button class="bah-btn-buscar" :disabled="!esValida" type="button" @click="irAKardex">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none"
          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        BUSCAR
      </button>
    </div>

    <!-- Dropdown autocomplete -->
    <Transition name="bah-drop">
      <div v-if="abierto" class="bah-dropdown" role="listbox">

        <!-- Resultados -->
        <template v-if="hayResultados">
          <button
            v-for="(alumno, i) in resultados"
            :key="numeroControl(alumno)"
            class="bah-item"
            :class="{ 'bah-item--activo': indiceActivo === i }"
            role="option"
            type="button"
            @click="seleccionar(alumno)"
            @mouseenter="indiceActivo = i"
          >
            <!-- Avatar iniciales -->
            <div class="bah-avatar">{{ iniciales(alumno) }}</div>

            <!-- Info -->
            <div class="bah-info">
              <span class="bah-nombre">{{ nombreCompleto(alumno) }}</span>
              <span class="bah-sub">
                {{ numeroControl(alumno) }}
                <span v-if="carrera(alumno)"> · {{ carrera(alumno) }}</span>
              </span>
            </div>

            <!-- Estatus badge -->
            <span class="bah-est" :class="estatusClass(alumno)">
              {{ (alumno.estatus ?? 'ACTIVO').toUpperCase() }}
            </span>

            <!-- Flecha -->
            <svg class="bah-flecha" xmlns="http://www.w3.org/2000/svg" width="11" height="11"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </template>

        <!-- Skeleton cargando -->
        <template v-else-if="cargando">
          <div v-for="i in 3" :key="i" class="bah-skeleton">
            <div class="sk-av"/>
            <div class="sk-lines">
              <div class="sk-line" style="width:60%"/>
              <div class="sk-line sk-line--sm" style="width:40%"/>
            </div>
            <div class="sk-badge"/>
          </div>
        </template>

        <!-- Sin resultados -->
        <div v-else-if="sinResultados" class="bah-estado">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
            viewBox="0 0 24 24" stroke="#9CA3AF" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <p class="bah-estado-txt">SIN RESULTADOS PARA "{{ termino.toUpperCase() }}"</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="bah-estado">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"
            viewBox="0 0 24 24" stroke="#F87171" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="bah-estado-txt" style="color:#F87171">{{ error.toUpperCase() }}</p>
        </div>

        <!-- Footer: tip navegación teclado -->
        <div v-if="hayResultados" class="bah-footer">
          <span>↑↓ navegar</span>
          <span>· Enter seleccionar</span>
          <span>· Esc cerrar</span>
        </div>

      </div>
    </Transition>

  </div>
</template>

<style scoped>
/* ── Wrapper ─────────────────────────────────────────────────────────── */
.bah-wrapper {
  position: relative;
  width: 100%;
}

/* ── Fila input + botón ──────────────────────────────────────────────── */
.bah-form {
  display: flex;
  gap: 8px;
}

.bah-input-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 8px;
  padding: 0 12px;
  height: 38px;
  transition: background .2s, border-color .2s;
}
.bah-input-wrap:hover {
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.25);
}
.bah-input-wrap--activo {
  background: rgba(255,255,255,.14);
  border-color: rgba(255,255,255,.40);
}

.bah-ico-lupa {
  color: rgba(255,255,255,.5);
  flex-shrink: 0;
}

.bah-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: .05em;
  -webkit-appearance: none;
}
.bah-input::placeholder {
  color: rgba(255,255,255,.40);
  font-size: 10px;
}
.bah-input::-webkit-search-cancel-button { display: none; }

.bah-ico-der {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.bah-spinner {
  color: rgba(255,255,255,.6);
  animation: bah-spin .75s linear infinite;
}
@keyframes bah-spin { to { transform: rotate(360deg); } }

.bah-btn-x {
  background: none;
  border: none;
  cursor: pointer;
  color: rgba(255,255,255,.5);
  display: flex;
  align-items: center;
  padding: 2px;
  border-radius: 4px;
  transition: color .15s;
}
.bah-btn-x:hover { color: #fff; }

.bah-btn-buscar {
  background: #1D52B7;
  border: none;
  border-radius: 8px;
  padding: 0 14px;
  height: 38px;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: .05em;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
  transition: background .15s;
  flex-shrink: 0;
}
.bah-btn-buscar:hover:not(:disabled) { background: #1A4184; }
.bah-btn-buscar:disabled { opacity: .45; cursor: not-allowed; }

/* ── Dropdown ────────────────────────────────────────────────────────── */
.bah-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  z-index: 9999;
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  overflow: hidden;
  box-shadow:
    0 20px 50px rgba(11,37,69,.18),
    0 6px 16px rgba(11,37,69,.10);
}

/* ── Item de resultado ───────────────────────────────────────────────── */
.bah-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
  border-bottom: 1px solid #F3F4F6;
  transition: background .12s;
}
.bah-item:last-of-type { border-bottom: none; }
.bah-item:hover,
.bah-item--activo { background: #EFF6FF; }

.bah-avatar {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: #0B2545;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.bah-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.bah-nombre {
  font-size: 11px;
  font-weight: 700;
  color: #1a1a2e;
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  letter-spacing: .02em;
}
.bah-sub {
  font-size: 9px;
  color: #6B7280;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: .03em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Badges de estatus */
.bah-est {
  font-size: 8px;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: .05em;
  padding: 3px 8px;
  border-radius: 20px;
  flex-shrink: 0;
  white-space: nowrap;
}
.est-activo   { background: rgba(39,174,96,.10);  color: #27AE60; }
.est-baja     { background: rgba(242,153,74,.10); color: #F2994A; }
.est-egresado { background: rgba(47,128,237,.10); color: #1D52B7; }
.est-otro     { background: rgba(130,130,130,.10);color: #828282; }

.bah-flecha {
  color: #D1D5DB;
  flex-shrink: 0;
  transition: color .12s;
}
.bah-item:hover .bah-flecha,
.bah-item--activo .bah-flecha { color: #1D52B7; }

/* ── Skeleton ────────────────────────────────────────────────────────── */
.bah-skeleton {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  animation: bah-pulse 1.4s ease-in-out infinite;
}
@keyframes bah-pulse { 0%,100%{opacity:1} 50%{opacity:.5} }

.sk-av {
  width: 34px; height: 34px;
  border-radius: 10px;
  background: #E2E8F0;
  flex-shrink: 0;
}
.sk-lines { flex:1; display:flex; flex-direction:column; gap:6px; }
.sk-line {
  height: 10px;
  background: #E2E8F0;
  border-radius: 4px;
}
.sk-line--sm { height: 8px; background: #F1F5F9; }
.sk-badge {
  width: 52px; height: 18px;
  border-radius: 20px;
  background: #E2E8F0;
  flex-shrink: 0;
}

/* ── Estado vacío / error ────────────────────────────────────────────── */
.bah-estado {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 28px 16px;
}
.bah-estado-txt {
  font-size: 10px;
  font-weight: 700;
  color: #9CA3AF;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: .05em;
  margin: 0;
  text-align: center;
}

/* ── Footer teclado ──────────────────────────────────────────────────── */
.bah-footer {
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: center;
  padding: 8px 12px;
  border-top: 1px solid #F3F4F6;
  background: #FAFBFC;
  font-size: 9px;
  color: #9CA3AF;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: .04em;
}

/* ── Transición dropdown ─────────────────────────────────────────────── */
.bah-drop-enter-active,
.bah-drop-leave-active {
  transition: opacity .18s ease, transform .18s ease;
}
.bah-drop-enter-from,
.bah-drop-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(.98);
}
</style>
