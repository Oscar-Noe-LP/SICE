<template>
  <div class="career-filters" role="toolbar" aria-label="FILTROS POR CARRERA">

    <!-- Botón TODAS -->
    <button
      class="cf-btn"
      :class="{ 'cf-btn--activo': carreraActiva === null }"
      @click="$emit('filtrar', null)"
      type="button"
      :aria-pressed="carreraActiva === null"
    >
      <span class="cf-ico cf-ico--all" aria-hidden="true">
        <LayoutGrid :size="15" />
      </span>
      <span class="cf-label">TODAS</span>
      <span class="cf-count" :class="{ 'cf-count--activo': carreraActiva === null }">
        {{ formatNum(totalAlumnos) }}
      </span>
    </button>

    <!-- Un botón por carrera -->
    <button
      v-for="(carrera, i) in carreras"
      :key="carrera.id_carrera"
      class="cf-btn"
      :class="{ 'cf-btn--activo': carreraActiva === carrera.id_carrera }"
      :style="carreraActiva === carrera.id_carrera
        ? { borderColor: colores[i % colores.length], background: hexRgba(colores[i % colores.length], 0.07) }
        : {}"
      @click="$emit('filtrar', carrera.id_carrera)"
      type="button"
      :aria-pressed="carreraActiva === carrera.id_carrera"
      :aria-label="`FILTRAR POR ${carrera.nombre}`"
    >
      <!-- Icono con color de carrera -->
      <span
        class="cf-ico"
        :style="{
          background: hexRgba(colores[i % colores.length], 0.12),
          color: colores[i % colores.length],
        }"
        aria-hidden="true"
      >
        <component :is="getIcono(carrera.nombre)" :size="15" />
      </span>

      <span class="cf-label">{{ abreviar(carrera.nombre) }}</span>

      <span
        class="cf-count"
        :style="carreraActiva === carrera.id_carrera
          ? { background: colores[i % colores.length], color: '#FFFFFF' }
          : {}"
      >
        {{ carrera.matriculas }}
      </span>
    </button>

  </div>
</template>

<script setup>
import {
  LayoutGrid,
  Monitor,
  Settings,
  Zap,
  Wrench,
  Briefcase,
  FlaskConical,
  BookOpen,
} from 'lucide-vue-next'

import { COLORES_CARRERA, formatNum } from '@/stores/dashboardStore.js'

const props = defineProps({
  carreras:      { type: Array,  default: () => [] },
  carreraActiva: { type: Number, default: null },
  totalAlumnos:  { type: Number, default: 0 },
})

defineEmits(['filtrar'])

const colores = COLORES_CARRERA

/** Abrevia el nombre para que quepa en el botón */
const abreviar = (nombre = '') =>
  nombre
    .replace('ING. SISTEMAS COMPUTACIONALES', 'SISTEMAS')
    .replace('ING. INDUSTRIAL',               'INDUSTRIAL')
    .replace('ING. ELECTRICA',                'ELECTRICA')
    .replace('ING. ELECTRONICA',              'ELECTRONICA')
    .replace('ING. MECANICA',                 'MECANICA')
    .replace('ING. BIOQUIMICA',               'BIOQUIMICA')
    .replace('LIC. GESTION EMPRESARIAL',      'G. EMPRESARIAL')
    .replace('ING. ',  '')
    .replace('LIC. ',  '')

/** Devuelve el componente Lucide según el nombre de la carrera */
const getIcono = (nombre = '') => {
  const n = nombre.toLowerCase()
  if (n.includes('sistem') || n.includes('comput')) return Monitor
  if (n.includes('industrial'))                      return Settings
  if (n.includes('electr'))                          return Zap
  if (n.includes('mecani') || n.includes('mecán'))   return Wrench
  if (n.includes('empresa') || n.includes('gesti'))  return Briefcase
  if (n.includes('bioqu'))                           return FlaskConical
  return BookOpen
}

/** Hex → rgba con alpha */
const hexRgba = (hex, alpha) => {
  if (!hex || hex.length < 7) return `rgba(29,82,183,${alpha})`
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  return `rgba(${r},${g},${b},${alpha})`
}
</script>

<style scoped>
.career-filters {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding: 2px 0;
}

/* ── Botón base ── */
.cf-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 11px 6px 7px;
  border-radius: 10px;
  border: 1.5px solid #E0E0E0;
  background: #FFFFFF;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  font-size: 10px;
  font-weight: 500;
  color: #828282;
  transition: all 0.15s ease;
  white-space: nowrap;
  line-height: 1;
}

.cf-btn:hover {
  border-color: #BDBDBD;
  color: #333333;
  background: #F4F6F9;
}

.cf-btn--activo {
  color: #333333;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* ── Icono ── */
.cf-ico {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: #F2F4F7;
  color: #828282;
  transition: all 0.15s;
}

.cf-ico--all {
  background: rgba(29,82,183,0.10);
  color: #1D52B7;
}

.cf-btn--activo .cf-ico--all {
  background: #1D52B7;
  color: #FFFFFF;
}

/* ── Label ── */
.cf-label {
  font-family: 'Montserrat', sans-serif;
  font-size: 10px;
  font-weight: inherit;
  color: inherit;
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  letter-spacing: 0.03em;
}

/* ── Conteo ── */
.cf-count {
  font-size: 9px;
  font-weight: 700;
  color: #828282;
  background: #F2F4F7;
  border-radius: 20px;
  padding: 2px 6px;
  flex-shrink: 0;
  font-family: 'Montserrat', sans-serif;
  transition: all 0.15s;
  min-width: 24px;
  text-align: center;
}

.cf-count--activo {
  background: #1D52B7;
  color: #FFFFFF;
}

/* ── Responsive: scroll horizontal en móvil ── */
@media (max-width: 768px) {
  .career-filters {
    overflow-x: auto;
    flex-wrap: nowrap;
    padding-bottom: 4px;
    scrollbar-width: none;
  }
  .career-filters::-webkit-scrollbar { display: none; }
  .cf-btn { flex-shrink: 0; }
}
</style>