<script setup lang="ts">
/**
 * TarjetaBusqueda.vue — Tarjeta de resultado del Buscador Global
 * Ubicación: src/components/search/TarjetaBusqueda.vue
 * Sprint 1 · Equipo 3
 */

import { computed } from 'vue'
import { User, GraduationCap, Hash, Building2, ChevronRight } from 'lucide-vue-next'

// ── Tipos exportados ───────────────────────────────────────────────────────
export type TipoResultado = 'ALUMNO' | 'DOCENTE'

export interface ResultadoBusqueda {
  /** Tipo de entidad encontrada */
  tipo: TipoResultado
  /** Nombre completo ya en MAYÚSCULAS */
  nombre_completo: string
  /** Número de control (alumno) o clave (docente) */
  identificador: string
  /** Carrera (alumno) o departamento (docente) */
  area?: string
  /** Estado actual del registro */
  estatus?: string
  /** ID original para navegación */
  id?: number | string
}

// ── Props y Emits ──────────────────────────────────────────────────────────
interface Props {
  resultado: ResultadoBusqueda
  /** Resalta las coincidencias del término buscado */
  termino?: string
}

const props = defineProps<Props>()
const emit  = defineEmits<{ seleccionar: [r: ResultadoBusqueda] }>()

// ── Helpers de tipo ────────────────────────────────────────────────────────
const esAlumno  = computed(() => props.resultado.tipo === 'ALUMNO')
const iconoTipo = computed(() => esAlumno.value ? GraduationCap : User)

const labelTipo = computed(() =>
  esAlumno.value ? 'Alumno' : 'Docente'
)

const estatusNorm = computed(() =>
  (props.resultado.estatus ?? '').toUpperCase()
)

const badgeClase = computed(() => {
  switch (estatusNorm.value) {
    case 'ACTIVO':    return 'badge-activo'
    case 'BAJA':      return 'badge-baja'
    case 'EGRESADO':  return 'badge-egresado'
    case 'IRREGULAR': return 'badge-irregular'
    default:          return 'badge-inactivo'
  }
})

// ── Resaltado de texto ─────────────────────────────────────────────────────
function resaltar(texto: string): string {
  if (!props.termino || props.termino.trim() === '') return texto
  const escaped = props.termino.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
  const re = new RegExp(`(${escaped})`, 'gi')
  return texto.replace(re, '<mark class="sice-mark">$1</mark>')
}
</script>

<template>
  <button
    type="button"
    class="tarjeta"
    @mousedown.prevent="emit('seleccionar', resultado)"
  >
    <!-- Avatar -->
    <div
      class="avatar"
      :class="esAlumno ? 'avatar--alumno' : 'avatar--docente'"
    >
      <component :is="iconoTipo" :size="18" stroke-width="2" />
    </div>

    <!-- Información principal -->
    <div class="info">
      <!-- Fila 1: nombre + badge alineados -->
      <div class="info-top">
        <p
          class="nombre"
          v-html="resaltar(resultado.nombre_completo)"
        />
        <span
          v-if="resultado.estatus"
          class="badge"
          :class="badgeClase"
        >
          {{ estatusNorm }}
        </span>
      </div>

      <!-- Fila 2: subtipo · identificador · área -->
      <div class="meta">
        <span class="meta-tipo">{{ labelTipo }}</span>
        <span class="meta-sep" aria-hidden="true">·</span>
        <span class="meta-item">
          <Hash :size="10" stroke-width="2" />
          <span>{{ resultado.identificador }}</span>
        </span>
        <template v-if="resultado.area">
          <span class="meta-sep" aria-hidden="true">·</span>
          <span class="meta-item meta-area">
            <Building2 :size="10" stroke-width="2" />
            <span>{{ resultado.area.toUpperCase() }}</span>
          </span>
        </template>
      </div>
    </div>

    <!-- Flecha -->
    <div class="flecha" aria-hidden="true">
      <ChevronRight :size="15" stroke-width="2.5" />
    </div>
  </button>
</template>

<style scoped>
/* ─── Variables locales ────────────────────────────────────────────────── */
.tarjeta {
  --azul:        #1D52B7;
  --azul-claro:  #2F80ED;
  --azul-bg:     #EEF4FF;
  --azul-border: #D8E5FF;
  --texto:       #111827;
  --texto-sub:   #64748B;
  --texto-meta:  #6B7280;
  --borde:       #E6ECF5;
  --radio:       8px;
}

/* ─── Tarjeta base ─────────────────────────────────────────────────────── */
.tarjeta {
  width: 100%;

  display: grid;

  grid-template-columns:
    40px
    minmax(0,1fr)
    16px;

  align-items: center;

  gap: 10px;

  padding: 10px 12px;

  min-height: 58px;

  background: #FFF;

  border: 1px solid #E5EAF2;
  border-radius: 12px;
}

.tarjeta:hover {
  border-color: rgba(29, 82, 183, .25);
  box-shadow: 0 2px 10px rgba(29, 82, 183, .07);
  background: #F8FBFF;
}

.tarjeta:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(29, 82, 183, .18);
}

.tarjeta:active {
  background: #EEF4FF;
}

/* ─── Avatar ───────────────────────────────────────────────────────────── */
.avatar {
  width: 34px;
  height: 34px;
  min-width: 34px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform .14s ease;
}

.tarjeta:hover .avatar {
  transform: scale(1.06);
}

.avatar--alumno {
  background: var(--azul-bg);
  color: var(--azul);
  border: 1px solid var(--azul-border);
}

.avatar--docente {
  background: #F0F8FF;
  color: var(--azul-claro);
  border: 1px solid #DDEEFF;
}

.info {
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
  overflow: hidden;
}

.info-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.nombre {
  margin: 0;

  font-size: 13px;
  font-weight: 700;

  color: #1F2937;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  line-height: 1.2;
}

/* ─── Badge (ahora junto al nombre) ────────────────────────────────────── */
.badge {
  display: inline-flex;
  align-items: center;
  flex-shrink: 0;
  height: 18px;
  padding: 0 7px;
  border-radius: 5px;
  font-family: 'Montserrat', sans-serif;
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: .05em;
  text-transform: uppercase;
  white-space: nowrap;
  border: 1px solid transparent;
}

.badge-activo    { background: #EDFBF2; color: #15803D; border-color: #BBF7D0; }
.badge-baja      { background: #FFF1F2; color: #BE123C; border-color: #FECDD3; }
.badge-egresado  { background: #EFF6FF; color: #1D4ED8; border-color: #BFDBFE; }
.badge-irregular { background: #FFFBEB; color: #B45309; border-color: #FDE68A; }
.badge-inactivo  { background: #F8FAFC; color: #64748B; border-color: #E2E8F0; }

/* ─── Meta ─────────────────────────────────────────────────────────────── */
.meta {
  display: flex;
  align-items: center;
  gap: 6px;

  min-width: 0;

  overflow: hidden;

  font-size: 11px;
  color: #64748B;

  white-space: nowrap;
}

.meta-item,
.meta-area,
.meta-tipo {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.meta-tipo {
  font-family: 'Montserrat', sans-serif;
  font-size: 11px;
  font-weight: 600;
  color: var(--texto-sub);
  white-space: nowrap;
  flex-shrink: 0;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-family: 'Montserrat', sans-serif;
  font-size: 11px;
  font-weight: 500;
  color: var(--texto-meta);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-shrink: 0;
}

.meta-area {
  flex-shrink: 1;
  min-width: 0;
}

.meta-area span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.meta-sep {
  color: #D1D5DB;
  font-size: 11px;
  flex-shrink: 0;
  line-height: 1;
}

/* ─── Flecha ───────────────────────────────────────────────────────────── */
.flecha {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #CBD5E1;
  flex-shrink: 0;
  transition: color .14s ease, transform .14s ease;
}

.tarjeta:hover .flecha {
  color: var(--azul);
  transform: translateX(2px);
}

/* ─── Resaltado de búsqueda ────────────────────────────────────────────── */
:deep(.sice-mark) {
  background: #FEF08A;
  color: #111827;
  padding: 1px 2px;
  border-radius: 3px;
  font-weight: 800;
}

/* ─── Responsive móvil ─────────────────────────────────────────────────── */
@media (max-width: 640px) {
  .tarjeta {
    grid-template-columns: 34px minmax(0, 1fr);
    gap: 9px;
    padding: 8px 12px;
  }

  .flecha {
    display: none;
  }

  .nombre {
    font-size: 12.5px;
  }

  .meta-area {
    display: none;
  }

  .meta-sep:last-of-type {
    display: none;
  }
}
</style>