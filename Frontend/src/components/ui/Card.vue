<script setup lang="ts">
/**
 * Card.vue — Componente base reutilizable del SICE Design System
 * Ubicación: src/components/ui/Card.vue
 * Sprint 1 · Equipo 3
 */

import { computed, useSlots } from 'vue'

// ── Tipos y Props ─────────────────────────────────────────────────────────────
export type CardVariant = 'default' | 'outlined' | 'elevated' | 'filled' | 'ghost'
export type CardSize    = 'sm' | 'md' | 'lg'
export type CardStatus  = 'activo' | 'inactivo' | 'baja' | 'egresado' | 'irregular' | null

interface Props {
  /** Título principal de la tarjeta */
  titulo?: string
  /** Subtítulo o descripción secundaria */
  subtitulo?: string
  /** Variante visual */
  variante?: CardVariant
  /** Tamaño de padding interno */
  tamanio?: CardSize
  /** Estado para mostrar badge de estatus */
  estatus?: CardStatus
  /** Permite hover interactivo (cursor pointer + transformación) */
  interactivo?: boolean
  /** Muestra divisor entre header y body */
  divisor?: boolean
  /** Clases extra para el wrapper raíz */
  clase?: string
  /** Carga esqueleto (skeleton loading) */
  cargando?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variante:    'default',
  tamanio:     'md',
  estatus:     null,
  interactivo: false,
  divisor:     false,
  cargando:    false,
})

const emit = defineEmits<{ click: [e: MouseEvent] }>()
const slots = useSlots()

// ── Mapas de clases estáticas ──────────────────────────────────────────────────
const varianteClases: Record<CardVariant, string> = {
  default:  'bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 shadow-card',
  outlined: 'bg-transparent border-2 border-surface-300 dark:border-surface-600',
  elevated: 'bg-white dark:bg-surface-800 shadow-elevated border border-transparent',
  filled:   'bg-primary-50 dark:bg-primary-950 border border-primary-200 dark:border-primary-800',
  ghost:    'bg-surface-50/60 dark:bg-surface-900/40 border border-surface-100 dark:border-surface-800',
}

const tamanioClases: Record<CardSize, string> = {
  sm: 'p-3',
  md: 'p-4',
  lg: 'p-6',
}

const estatusMapa: Record<NonNullable<CardStatus>, { label: string; clase: string }> = {
  activo:    { label: 'ACTIVO',    clase: 'badge-activo'    },
  inactivo:  { label: 'INACTIVO',  clase: 'badge-inactivo'  },
  baja:      { label: 'BAJA',      clase: 'badge-baja'      },
  egresado:  { label: 'EGRESADO',  clase: 'badge-egresado'  },
  irregular: { label: 'IRREGULAR', clase: 'badge-irregular' },
}

// ── Computados ────────────────────────────────────────────────────────────────
const estatusInfo = computed(() => props.estatus ? estatusMapa[props.estatus] : null)
const tieneHeader  = computed(() => props.titulo || props.subtitulo || props.estatus || !!slots.header)
const tieneFooter  = computed(() => !!slots.footer)

// ── Manejo de Eventos Seguro ──────────────────────────────────────────────────
function manejarAccion(event: MouseEvent | KeyboardEvent) {
  if (props.interactivo) {
    // Forzamos el casteo de forma segura aquí dentro de la sección script de TS
    emit('click', event as MouseEvent)
  }
}
</script>

<template>
  <div
    :class="[
      'sice-card rounded-xl transition-all duration-200',
      varianteClases[variante],
      tamanioClases[tamanio],
      interactivo && 'cursor-pointer hover:-translate-y-0.5 hover:shadow-hover active:scale-[0.99]',
      cargando    && 'pointer-events-none select-none',
      clase,
    ]"
    v-bind="interactivo ? { tabindex: 0, role: 'button' } : {}"
    @click="manejarAccion"
    @keydown.enter="manejarAccion"
  >

    <template v-if="cargando">
      <div class="space-y-3 animate-pulse">
        <div class="h-4 w-2/3 rounded bg-surface-200 dark:bg-surface-700" />
        <div class="h-3 w-1/2 rounded bg-surface-200 dark:bg-surface-700" />
        <div class="h-3 w-full rounded bg-surface-100 dark:bg-surface-800" />
      </div>
    </template>

    <template v-else>
      <div v-if="tieneHeader" class="card-header mb-3">
        <slot name="header">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
              <p
                v-if="titulo"
                class="card-titulo truncate font-display font-semibold tracking-wide text-surface-900 dark:text-surface-50 uppercase"
                :class="tamanio === 'lg' ? 'text-base' : 'text-sm'"
              >
                {{ titulo }}
              </p>
              <p
                v-if="subtitulo"
                class="mt-0.5 text-xs text-surface-500 dark:text-surface-400 uppercase tracking-wider truncate"
              >
                {{ subtitulo }}
              </p>
            </div>
            <span v-if="estatusInfo" :class="['badge shrink-0', estatusInfo.clase]">
              {{ estatusInfo.label }}
            </span>
          </div>
        </slot>
      </div>

      <hr
        v-if="divisor && tieneHeader"
        class="border-surface-200 dark:border-surface-700 mb-3 -mx-1"
      />

      <div v-if="slots.default" class="card-body">
        <slot />
      </div>

      <div
        v-if="tieneFooter"
        class="card-footer mt-3 pt-3 border-t border-surface-100 dark:border-surface-700 flex items-center justify-between gap-2"
      >
        <slot name="footer" />
      </div>
    </template>

  </div>
</template>

<style lang="postcss" scoped>
/* ── Sombras personalizadas ────────────────────────────── */
.shadow-card    { box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05); }
.shadow-elevated{ box-shadow: 0 4px 16px rgba(0,0,0,.10), 0 1px 4px rgba(0,0,0,.06); }
.shadow-hover   { box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.08); }

/* ── Badges de estatus ─────────────────────────────────── */
.badge {
  @apply text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full border;
}
.badge-activo    { @apply bg-emerald-50  text-emerald-700  border-emerald-200  dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800; }
.badge-inactivo  { @apply bg-surface-100 text-surface-500  border-surface-200  dark:bg-surface-800 dark:text-surface-400 dark:border-surface-700; }
.badge-baja      { @apply bg-red-50     text-red-700      border-red-200      dark:bg-red-950 dark:text-red-400 dark:border-red-800; }
.badge-egresado  { @apply bg-blue-50    text-blue-700     border-blue-200     dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800; }
.badge-irregular { @apply bg-amber-50   text-amber-700    border-amber-200    dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800; }
</style>