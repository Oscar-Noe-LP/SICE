<template>
  <div
    class="cc"
    :class="{ 'cc--activa': activa }"
    :style="activa
      ? { borderColor: color, boxShadow: `0 4px 20px ${hexRgba(color, 0.18)}` }
      : {}"
    @click="$emit('click')"
    role="button"
    tabindex="0"
    :aria-label="`VER DETALLE DE ${carrera.nombre}`"
    @keydown.enter="$emit('click')"
    @keydown.space.prevent="$emit('click')"
  >
    <!-- Franja de color superior -->
    <div class="cc__barra" :style="{ background: color }" aria-hidden="true"></div>

    <!-- Encabezado: icono + nombre + grupos -->
    <div class="cc__header">
      <div
        class="cc__ico"
        :style="{ background: hexRgba(color, 0.12), color }"
        aria-hidden="true"
      >
        <component :is="icono" :size="20" />
      </div>

      <div class="cc__titulo-wrap">
        <h3 class="cc__titulo">{{ carrera.nombre }}</h3>
        <span class="cc__grupos-pill">
          <Users :size="10" />
          {{ carrera.grupos }} GRUPO{{ carrera.grupos !== 1 ? 'S' : '' }}
        </span>
      </div>
    </div>

    <!-- Número principal: total matrículas -->
    <div class="cc__matriculas">
      <span class="cc__num" :style="{ color }">{{ formatNum(carrera.matriculas) }}</span>
      <span class="cc__num-label">MATRICULAS TOTALES</span>
    </div>

    <!-- Barra visual regulares vs irregulares -->
    <div
      class="cc__barra-estado"
      role="img"
      :aria-label="`${pctReg}% regulares, ${pctIrreg}% irregulares`"
    >
      <div
        class="cc__fill cc__fill--reg"
        :style="{ width: pctReg + '%' }"
      ></div>
      <div
        class="cc__fill cc__fill--irreg"
        :style="{ width: pctIrreg + '%' }"
      ></div>
    </div>

    <!-- Chips regulares / irregulares -->
    <div class="cc__chips">

      <div class="cc__chip">
        <span class="cc__dot cc__dot--reg" aria-hidden="true"></span>
        <div class="cc__chip-info">
          <span class="cc__chip-num">{{ formatNum(carrera.regulares) }}</span>
          <span class="cc__chip-label">REGULARES</span>
        </div>
        <span class="cc__chip-pct cc__chip-pct--verde">{{ pctReg }}%</span>
      </div>

      <div class="cc__chip">
        <span class="cc__dot cc__dot--irreg" aria-hidden="true"></span>
        <div class="cc__chip-info">
          <span class="cc__chip-num">{{ formatNum(carrera.irregulares) }}</span>
          <span class="cc__chip-label">IRREGULARES</span>
        </div>
        <span class="cc__chip-pct cc__chip-pct--rojo">{{ pctIrreg }}%</span>
      </div>

    </div>

    <!-- Footer: ver grupos -->
    <div class="cc__footer">
      <span class="cc__ver" :style="{ color }">
        VER GRUPOS
        <ChevronRight :size="13" />
      </span>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  Monitor, Settings, Zap, Wrench,
  Briefcase, FlaskConical, BookOpen,
  Users, ChevronRight,
} from 'lucide-vue-next'

import { formatNum } from '@/stores/dashboardStore.js'

const props = defineProps({
  carrera: { type: Object,  required: true },
  color:   { type: String,  default: '#1D52B7' },
  icono:   { type: Object,  default: () => BookOpen }, // componente Lucide
  activa:  { type: Boolean, default: false },
})

defineEmits(['click'])

const pctReg   = computed(() => {
  if (!props.carrera.matriculas) return 0
  return Math.round((props.carrera.regulares / props.carrera.matriculas) * 100)
})
const pctIrreg = computed(() => props.carrera.matriculas ? 100 - pctReg.value : 0)

const hexRgba = (hex, alpha) => {
  if (!hex || hex.length < 7) return `rgba(29,82,183,${alpha})`
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  return `rgba(${r},${g},${b},${alpha})`
}
</script>

<style scoped>
/* ── Card contenedor ── */
.cc {
  background: #FFFFFF;
  border: 1.5px solid #E0E0E0;
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(29,82,183,0.05);
  position: relative;
}

.cc:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.10);
  border-color: #BDBDBD;
}

.cc--activa { transform: translateY(-2px); }

/* ── Franja color ── */
.cc__barra { height: 4px; width: 100%; flex-shrink: 0; }

/* ── Header ── */
.cc__header {
  display: flex;
  align-items: flex-start;
  gap: 11px;
  padding: 15px 15px 10px;
}

.cc__ico {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.cc__titulo-wrap {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.cc__titulo {
  font-size: 11px;
  font-weight: 700;
  color: #333333;
  font-family: 'Montserrat', sans-serif;
  line-height: 1.35;
  margin: 0;
  letter-spacing: 0.02em;
}

.cc__grupos-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #F2F4F7;
  border-radius: 20px;
  padding: 2px 8px;
  font-size: 9px;
  font-weight: 700;
  color: #828282;
  font-family: 'Montserrat', sans-serif;
  width: fit-content;
  letter-spacing: 0.04em;
}

/* ── Matrículas ── */
.cc__matriculas {
  display: flex;
  align-items: baseline;
  gap: 6px;
  padding: 0 15px 11px;
}

.cc__num {
  font-size: 36px;
  font-weight: 700;
  line-height: 1;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: -0.5px;
}

.cc__num-label {
  font-size: 10px;
  color: #828282;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: 0.04em;
  margin-bottom: 2px;
}

/* ── Barra estado ── */
.cc__barra-estado {
  display: flex;
  height: 7px;
  margin: 0 15px 13px;
  border-radius: 99px;
  overflow: hidden;
  background: #F2F4F7;
  gap: 2px;
}

.cc__fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.cc__fill--reg   { background: #27AE60; }
.cc__fill--irreg { background: #EB5757; }

/* ── Chips ── */
.cc__chips {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 7px;
  padding: 0 15px 13px;
}

.cc__chip {
  display: flex;
  align-items: center;
  gap: 7px;
  background: #F4F6F9;
  border-radius: 8px;
  padding: 8px 9px;
}

.cc__dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}
.cc__dot--reg   { background: #27AE60; }
.cc__dot--irreg { background: #EB5757; }

.cc__chip-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.cc__chip-num {
  font-size: 15px;
  font-weight: 700;
  color: #333333;
  font-family: 'Montserrat', sans-serif;
  line-height: 1;
}

.cc__chip-label {
  font-size: 8px;
  font-weight: 700;
  color: #828282;
  font-family: 'Montserrat', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.cc__chip-pct {
  font-size: 11px;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  flex-shrink: 0;
}

.cc__chip-pct--verde { color: #27AE60; }
.cc__chip-pct--rojo  { color: #EB5757; }

/* ── Footer ── */
.cc__footer {
  padding: 9px 15px 13px;
  border-top: 1px solid #F2F4F7;
  margin-top: auto;
}

.cc__ver {
  font-size: 10px;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  display: flex;
  align-items: center;
  gap: 4px;
  letter-spacing: 0.04em;
  transition: gap 0.15s;
}

.cc:hover .cc__ver { gap: 7px; }
</style>